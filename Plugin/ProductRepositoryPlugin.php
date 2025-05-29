<?php
declare(strict_types=1);
namespace Convertcart\Analytics\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\Data\ProductExtensionFactory;

/**
 * Plugin to add stock data to product extension attributes after product list retrieval.
 */
class ProductRepositoryPlugin
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected ResourceConnection $resourceConnection;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;
    /**
     * @var ProductExtensionFactory
     */
    protected ProductExtensionFactory $extensionFactory;
    /**
     * ProductRepositoryPlugin constructor.
     *
     * @param ResourceConnection      $resourceConnection Resource connection
     * @param LoggerInterface         $logger             Logger
     * @param ProductExtensionFactory $extensionFactory   Product extension factory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        ProductExtensionFactory $extensionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->extensionFactory = $extensionFactory;
    }
    /**
     * Add stock data to product extension attributes after product list retrieval.
     *
     * @param  ProductRepositoryInterface    $subject
     * @param  ProductSearchResultsInterface $searchResults
     * @return ProductSearchResultsInterface
     */
    /**
     * Add stock data to product extension attributes after product list retrieval.
     *
     * @param  ProductRepositoryInterface    $subject       Product repository
     * @param  ProductSearchResultsInterface $searchResults Search results
     * @return ProductSearchResultsInterface
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        ProductSearchResultsInterface $searchResults
    ): ProductSearchResultsInterface {
        $products = $searchResults->getItems();
        if (empty($products)) {
            return $searchResults;
        }
        $skus = [];
        foreach ($products as $product) {
            $skus[] = $product->getSku();
        }
        if (empty($skus)) {
            return $searchResults;
        }
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName('cataloginventory_stock_item');

            $query = $connection->select()
                ->from($tableName, ['product_id', 'qty', 'is_in_stock', 'manage_stock', 'backorders'])
                ->where('product_id IN (?)', array_map(fn($p) => $p->getId(), $products));
            $stockData = $connection->fetchAll($query);

            $stockMap = [];
            foreach ($stockData as $row) {
                $stockMap[$row['product_id']] = $row;
            }
            foreach ($products as $product) {
                $productId = $product->getId();
                if (isset($stockMap[$productId])) {
                    $extensionAttributes = $product->getExtensionAttributes();
                    if ($extensionAttributes === null) {
                        $extensionAttributes = $this->extensionFactory->create();
                    }
                    $extensionAttributes->setQty((float)$stockMap[$productId]['qty']);
                    $extensionAttributes->setManageStock((bool)$stockMap[$productId]['manage_stock']);
                    $extensionAttributes->setIsInStock((bool)$stockMap[$productId]['is_in_stock']);
                    $extensionAttributes->setBackorders((int)$stockMap[$productId]['backorders']);
                    $product->setExtensionAttributes($extensionAttributes);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                "Product Plugin: Error fetching stock data: " . $e->getMessage() . "\n" . $e->getTraceAsString()
            );
        }
        return $searchResults;
    }
}
