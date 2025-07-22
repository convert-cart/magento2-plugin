<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Api\Data;

interface SyncInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const ID = 'id';
    public const ITEM_ID = 'item_id';
    public const TYPE = 'type';
    public const CREATED_AT = 'created_at';

    public function getId(): ?int;

    public function setId(int $id): self;

    public function getType(): ?string;

    public function setType(string $type): self;

    public function getItemId(): ?string;

    public function setItemId(string $itemId): self;

    public function getCreatedAt(): ?string;

    public function setCreatedAt(string $createdAt): self;
}
