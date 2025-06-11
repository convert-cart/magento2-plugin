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

            // check if inventory_source table exists then fetch the inventory source & check if multiple sources are enabled
            $sourceTable = $connection->getTableName('inventory_source');
            $sourceItemTable = $connection->getTableName('inventory_source_item');
            $sourceExists = $connection->isTableExists($sourceTable);
            $sourceItemTableExists = $connection->isTableExists($sourceItemTable);
            $msiEnabled = false;
            $msiStockData = [];

            if ($sourceExists) {
                $sourceQuery = $connection->select()
                    ->from($sourceTable, ['source_code'])
                    ->where('enabled = ?', 1); // Assuming 'enabled' is the column to check if source is enabled
                $sources = $connection->fetchCol($sourceQuery);
                if (count($sources) > 1) {
                    $msiEnabled = true;
                }
            }

            if ($msiEnabled && $sourceItemTableExists) {
                // Fetch stock data from inventory_source table
                $sourceItemTable = $connection->getTableName('inventory_source_item');
                $sourceItemQuery = $connection->select()
                    ->from($sourceItemTable, ['sku', 'quantity', 'status', 'source_code'])
                    ->where('sku IN (?)', $skus);
                $msiStockData = $connection->fetchAll($sourceItemQuery);
            }

            // // If MSI is enabled, create a mapping of SKU to stock data
            // $msiStockMap = [];
            if ($msiEnabled && $sourceItemTableExists) {
                foreach ($msiStockData as $row) {
                    $sku = $row['sku'];
                    $sourceCode = $row['source_code'];

                    if (!isset($msiStockMap[$sku])) {
                        $msiStockMap[$sku] = [];
                    }
                    if (!isset($msiStockMap[$sku][$sourceCode])) {
                        $msiStockMap[$sku][$sourceCode] = [];
                    }
                    // Assuming quantity is the stock quantity, status is 1 for in stock, and manage_stock is always true
                    // Backorders are not considered in MSI, so setting it to 0

                    $msiStockMap[$sku][$sourceCode] = [
                        'qty' => (float)$row['quantity'],
                        'is_in_stock' => (int)$row['status'] === 1,
                        'manage_stock' => true, // Assuming manage stock is always true for MSI
                        'backorders' => 0 // Assuming no backorders for MSI
                    ];
                }
            }

            $stockMap = [];
            foreach ($stockData as $row) {
                $stockMap[$row['product_id']] = $row;
            }
            foreach ($products as $product) {
                $productId = $product->getId();
                $productSku = $product->getSku();
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

                if ($msiEnabled && $sourceItemTableExists && isset($msiStockMap[$productSku])) {
                    $msiExtensionAttributes = $product->getExtensionAttributes();
                    if ($msiExtensionAttributes === null) {
                        $msiExtensionAttributes = $this->extensionFactory->create();
                    }
                    $msiExtensionAttributes->setMsiStockData(json_encode($msiStockMap[$productSku]));
                    $product->setExtensionAttributes($msiExtensionAttributes);
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
