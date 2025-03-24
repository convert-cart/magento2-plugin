<?php

namespace Convertcart\Analytics\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class ProductRepositoryPlugin
{
    protected $resourceConnection;
    protected $logger;

    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    public function afterGetList(ProductRepositoryInterface $subject, ProductSearchResultsInterface $searchResults)
    {
        $this->logger->info('ProductRepositoryPlugin::afterGetList - Start processing');

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

            

            // Assign stock data to products
            foreach ($products as $product) {
                $productId = $product->getId();
                $this->logger->info("processing prod: " . $productId);
                if (isset($stockMap[$productId])) {
                    $this->logger->info("adding ext prod: " . $productId);
                    // $product->addData([
                    //     'stock_qty' => $stockMap[$productId]['qty'],
                    //     'is_in_stock' => $stockMap[$productId]['is_in_stock'],
                    //     'manage_stock' => $stockMap[$productId]['manage_stock'],
                    //     'backorders' => $stockMap[$productId]['backorders'],
                    // ]);

                    $extensionattributes = $product->getExtensionAttributes();
                    $extensionattributes->setQty($stockMap[$productId]['qty']);
                    $product->setExtensionAttributes($extensionattributes);
                }
            }

            $this->logger->info("Stock data added for " . count($products) . " products.");
        } catch (\Exception $e) {
            $this->logger->error("Error fetching stock data: " . $e->getMessage());
        }

        return $searchResults;
    }
}
