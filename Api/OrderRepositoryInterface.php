<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Api;

interface OrderRepositoryInterface
{
    /**
     * Get orders for ConvertCart synchronization
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getOrders(int $limit = 100, int $page = 1): array;
}
