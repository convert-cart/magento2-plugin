<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Api;

interface CustomerRepositoryInterface
{
    /**
     * Get customers for ConvertCart synchronization
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getCustomers(int $limit = 100, int $page = 1): array;
}
