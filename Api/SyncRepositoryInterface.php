<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Api;

use Convertcart\Analytics\Api\Data\SyncInterface;

interface SyncRepositoryInterface
{
    /**
     * Post Api data.
     *
     * @api
     *
     * @param int $limit
     * @param int $id
     * @param string $type
     *
     * @return array
     */
    /**
     * Post Api data.
     *
     * @api
     *
     * @param int      $limit
     * @param int|null $id
     * @param string   $type
     *
     * @return array|null
     */
    public function getDeletedProduct(int $limit, ?int $id, string $type): ?array;
}
