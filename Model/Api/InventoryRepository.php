<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Api\InventoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class InventoryRepository implements InventoryRepositoryInterface
{
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private StockRegistryInterface $stockRegistry;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockRegistryInterface $stockRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockRegistry = $stockRegistry;
    }

    public function getInventory(int $limit = 100, int $page = 1): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize($limit)
            ->setCurrentPage($page)
            ->create();

        $products = $this->productRepository->getList($searchCriteria);
        $result = [];

        foreach ($products->getItems() as $product) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            
            $result[] = [
                'product_id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'qty' => $stockItem->getQty(),
                'is_in_stock' => $stockItem->getIsInStock(),
                'manage_stock' => $stockItem->getManageStock(),
                'backorders' => $stockItem->getBackorders(),
                'updated_at' => $product->getUpdatedAt()
            ];
        }

        return [
            'inventory' => $result,
            'total_count' => $products->getTotalCount(),
            'page' => $page,
            'limit' => $limit
        ];
    }
}
