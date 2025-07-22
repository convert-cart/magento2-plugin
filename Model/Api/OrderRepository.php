<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface as MagentoOrderRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class OrderRepository implements OrderRepositoryInterface
{
    private MagentoOrderRepository $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        MagentoOrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getOrders(int $limit = 100, int $page = 1): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize($limit)
            ->setCurrentPage($page)
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria);
        $result = [];

        foreach ($orders->getItems() as $order) {
            $items = [];
            foreach ($order->getAllVisibleItems() as $item) {
                $items[] = [
                    'product_id' => $item->getProductId(),
                    'sku' => $item->getSku(),
                    'name' => $item->getName(),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQtyOrdered()
                ];
            }

            $result[] = [
                'id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'customer_email' => $order->getCustomerEmail(),
                'customer_id' => $order->getCustomerId(),
                'status' => $order->getStatus(),
                'state' => $order->getState(),
                'grand_total' => $order->getGrandTotal(),
                'currency_code' => $order->getOrderCurrencyCode(),
                'created_at' => $order->getCreatedAt(),
                'updated_at' => $order->getUpdatedAt(),
                'items' => $items
            ];
        }

        return [
            'orders' => $result,
            'total_count' => $orders->getTotalCount(),
            'page' => $page,
            'limit' => $limit
        ];
    }
}
