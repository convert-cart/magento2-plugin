<?php

namespace Convertcart\Analytics\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class ProductRepositoryPlugin
{
    protected $stockItemRepository;
    protected $searchCriteriaBuilder;
    protected $logger;

    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    public function afterGetList(ProductRepositoryInterface $subject, ProductSearchResultsInterface $searchResults)
    {
        $this->logger->info('ProductRepositoryPlugin::afterGetList - Start processing');

        $productSkus = [];

        foreach ($searchResults->getItems() as $product) {
            $productSkus[$product->getId()] = $product->getSku();
        }

        $this->logger->info('Collected SKUs: ' . implode(',', $productSkus));

        if (!empty($productSkus)) {
            try {
                // Fetch stock data in bulk
                $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', array_values($productSkus), 'in')->create();
                $stockItems = $this->stockItemRepository->getList($searchCriteria)->getItems();

                $this->logger->info('Fetched stock items count: ' . count($stockItems));

                // Map stock data to products
                foreach ($searchResults->getItems() as $product) {
                    foreach ($stockItems as $stockItem) {
                        if ($stockItem->getSku() === $product->getSku()) {
                            $product->setData('stock_qty', $stockItem->getQty());
                            $product->setData('is_in_stock', $stockItem->getIsInStock());
                            $product->setData('manage_stock', $stockItem->getManageStock());
                            $product->setData('backorders', $stockItem->getBackorders());

                            $this->logger->info("Updated stock details for SKU: {$product->getSku()}");
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('Error in ProductRepositoryPlugin::afterGetList - ' . $e->getMessage());
            }
        }

        $this->logger->info('ProductRepositoryPlugin::afterGetList - Processing completed');
        
        return $searchResults;
    }
}
