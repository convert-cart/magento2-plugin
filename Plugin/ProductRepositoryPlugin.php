<?php

namespace Convertcart\Analytics\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;

class ProductRepositoryPlugin
{
    protected $stockItemRepository;
    protected $stockItemCriteria;

    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterface $stockItemCriteria
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteria = $stockItemCriteria;
    }

    public function afterGetList(ProductRepositoryInterface $subject, ProductSearchResultsInterface $searchResults)
    {
        $productSkus = [];

        foreach ($searchResults->getItems() as $product) {
            $productSkus[] = $product->getSku();
        }

        if (!empty($productSkus)) {
            // Fetch stock items in bulk
            $criteria = $this->stockItemCriteria->setSkus($productSkus);
            $stockItems = $this->stockItemRepository->getList($criteria)->getItems();

            // Map stock data to products
            $stockData = [];
            foreach ($stockItems as $stockItem) {
                $stockData[$stockItem->getSku()] = $stockItem;
            }

            foreach ($searchResults->getItems() as $product) {
                if (isset($stockData[$product->getSku()])) {
                    $stockItem = $stockData[$product->getSku()];
                    $product->setData('stock_qty', $stockItem->getQty());
                    $product->setData('is_in_stock', $stockItem->getIsInStock());
                    $product->setData('manage_stock', $stockItem->getManageStock());
                    $product->setData('backorders', $stockItem->getBackorders());
                }
            }
        }

        return $searchResults;
    }
}
