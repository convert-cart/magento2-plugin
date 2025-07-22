<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Api;

interface InventoryRepositoryInterface
{
    /**
     * Get inventory data for ConvertCart synchronization
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getInventory(int $limit = 100, int $page = 1): array;
}
