<?php

namespace Convertcart\Analytics\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder; // ✅ Correct dependency
use Psr\Log\LoggerInterface;

class ProductRepositoryPlugin
{
    protected $stockItemRepository;
    protected $searchCriteriaBuilder;
    protected $logger;

    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder, // ✅ Use SearchCriteriaBuilder
        LoggerInterface $logger
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    public function afterGetList(ProductRepositoryInterface $subject, ProductSearchResultsInterface $searchResults)
    {
        $this->logger->info('ProductRepositoryPlugin::afterGetList - Start processing: 24mar');

        $skus = [];
        foreach ($searchResults->getItems() as $product) {
            $skus[] = $product->getSku();
        }

        if (empty($skus)) {
            return $searchResults;
        }

        $this->logger->info('Collected SKUs: 24mar: ' . implode(',', $skus));

        try {
            // ✅ Use SearchCriteriaBuilder to filter by SKU
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('sku', $skus, 'in') // ✅ Correct filter
                ->create();

            // Fetch all stock data in one go
            $stockItems = $this->stockItemRepository->getList($searchCriteria)->getItems();

            $stockData = [];
            foreach ($stockItems as $stockItem) {
                $stockData[$stockItem->getSku()] = [
                    'stock_qty' => $stockItem->getQty(),
                    'is_in_stock' => $stockItem->getIsInStock(),
                    'manage_stock' => $stockItem->getManageStock(),
                    'backorders' => $stockItem->getBackorders(),
                ];
            }

            // Assign stock data to products
            foreach ($searchResults->getItems() as $product) {
                $sku = $product->getSku();
                if (isset($stockData[$sku])) {
                    $product->addData($stockData[$sku]);
                }
            }

            $this->logger->info("Stock data added for " . count($skus) . " products.");
        } catch (\Exception $e) {
            $this->logger->error("Error fetching stock data: " . $e->getMessage());
        }

        return $searchResults;
    }
}
