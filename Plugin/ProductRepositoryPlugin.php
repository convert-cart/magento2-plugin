<?php

namespace Convertcart\Analytics\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductRepositoryPlugin
{
    protected $stockItemRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function afterGetList(ProductRepositoryInterface $subject, ProductSearchResultsInterface $searchResults)
    {
        $productSkus = [];

        foreach ($searchResults->getItems() as $product) {
            $productSkus[$product->getId()] = $product->getSku();
        }

        if (!empty($productSkus)) {
            // Fetch stock data in bulk
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', array_values($productSkus), 'in')->create();
            $stockItems = $this->stockItemRepository->getList($searchCriteria)->getItems();

            // Map stock data to products
            foreach ($searchResults->getItems() as $product) {
                foreach ($stockItems as $stockItem) {
                    if ($stockItem->getSku() === $product->getSku()) {
                        $product->setData('stock_qty', $stockItem->getQty());
                        $product->setData('is_in_stock', $stockItem->getIsInStock());
                        $product->setData('manage_stock', $stockItem->getManageStock());
                        $product->setData('backorders', $stockItem->getBackorders());
                        break;
                    }
                }
            }
        }

        return $searchResults;
    }
}
