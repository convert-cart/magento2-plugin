<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

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
     * ProductRepositoryPlugin constructor.
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Add stock data to product extension attributes after product list retrieval.
     * @param ProductRepositoryInterface $subject
     * @param ProductSearchResultsInterface $searchResults
     * @return ProductSearchResultsInterface
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        ProductSearchResultsInterface $searchResults
    ): ProductSearchResultsInterface
    {
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

            // Fetch stock data for all SKUs in a single query
            $query = $connection->select()
                ->from($tableName, ['product_id', 'qty', 'is_in_stock', 'manage_stock', 'backorders'])
                ->where('product_id IN (?)', array_map(fn($p) => $p->getId(), $products));

            $stockData = $connection->fetchAll($query);

            // Map stock data by product ID
            $stockMap = [];
            foreach ($stockData as $row) {
                $stockMap[$row['product_id']] = $row;
            }

            foreach ($products as $product) {
                $productId = $product->getId();
                $this->logger->info("processing prod: " . $productId);
                if (isset($stockMap[$productId])) {
                    $extensionAttributes = $product->getExtensionAttributes();
                    $extensionAttributes->setQty($stockMap[$productId]['qty']);
                    $extensionAttributes->setManageStock($stockMap[$productId]['manage_stock']);
                    $extensionAttributes->setIsInStock($stockMap[$productId]['is_in_stock']);
                    $extensionAttributes->setBackorders($stockMap[$productId]['backorders']);
                    $product->setExtensionAttributes($extensionAttributes);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("Product Plugin: Error fetching stock data: " . $e->getMessage());
        }

        return $searchResults;
    }
}
