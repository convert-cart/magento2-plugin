<?php

namespace Convertcart\Analytics\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory; // ✅ Correct factory for criteria
use Psr\Log\LoggerInterface;

class ProductRepositoryPlugin
{
    protected $stockItemRepository;
    protected $stockItemCriteriaFactory;
    protected $logger;

    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory, // ✅ Use StockItemCriteriaInterfaceFactory
        LoggerInterface $logger
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->logger = $logger;
    }

    public function afterGetList(ProductRepositoryInterface $subject, ProductSearchResultsInterface $searchResults)
    {
        $this->logger->info('ProductRepositoryPlugin::afterGetList - Start processing');

        $skus = [];
        foreach ($searchResults->getItems() as $product) {
            $skus[] = $product->getSku();
        }

        if (empty($skus)) {
            return $searchResults;
        }

        $this->logger->info('Collected SKUs: ' . implode(',', $skus));

        try {
            // ✅ Use StockItemCriteriaFactory instead of SearchCriteriaBuilder
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setSkus($skus); // ✅ Correct method for filtering stock items by SKU

            // Fetch all stock data
            $stockItems = $this->stockItemRepository->getList($criteria)->getItems();

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
