<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model;

use Magento\Framework\Model\AbstractModel;
use Convertcart\Analytics\Api\Data\SyncInterface;

class SyncManagement extends AbstractModel implements SyncInterface, \Magento\Framework\DataObject\IdentityInterface
{
    public const CACHE_TAG = 'Convertcart_Analytics';

    protected function _construct()
    {
        $this->_init(\Convertcart\Analytics\Model\ResourceModel\Sync::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId(): ?int
    {
        return $this->getData(self::ID);
    }

    public function setId($id): SyncInterface
    {
        return $this->setData(self::ID, $id);
    }

    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    public function setType(string $type): SyncInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    public function getItemId(): ?string
    {
        return $this->getData(self::ITEM_ID);
    }

    public function setItemId(string $itemId): SyncInterface
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt): SyncInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
