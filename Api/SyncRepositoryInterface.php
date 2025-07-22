<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Api;

interface SyncRepositoryInterface
{
    public function getDeletedProduct(int $limit, ?int $id, string $type): ?array;
}
