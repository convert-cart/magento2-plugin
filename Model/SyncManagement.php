<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model;

use Convertcart\Analytics\Api\Data\SyncInterface;

class SyncManagement implements SyncInterface, \Magento\Framework\DataObject\IdentityInterface
{

    public const CACHE_TAG = 'Convertcart_Analytics';

    protected function _construct()
    {
        $this->_init(\Convertcart\Analytics\Model\ResourceModel\Sync::class);
    }

    /**
     * Get cache identities.
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return SyncInterface
     */
    /**
     * Set ID.
     *
     * @param  int $id
     * @return SyncInterface
     */
    public function setId(int $id): SyncInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get TYPE.
     *
     * @return string
     */
    /**
     * Get TYPE.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return SyncInterface
     */
    /**
     * Set type.
     *
     * @param  string $type
     * @return SyncInterface
     */
    public function setType(string $type): SyncInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get itemId.
     *
     * @return string|null
     */
    /**
     * Get itemId.
     *
     * @return string|null
     */
    public function getItemId(): ?string
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * Set itemId.
     *
     * @param string $itemId
     *
     * @return SyncInterface
     */
    /**
     * Set itemId.
     *
     * @param  string $itemId
     * @return SyncInterface
     */
    public function setItemId(string $itemId): SyncInterface
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * Get createdAt.
     *
     * @return string
     */
    /**
     * Get createdAt.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set createdAt.
     *
     * @param string $createdAt
     *
     * @return SyncInterface
     */
    /**
     * Set createdAt.
     *
     * @param  string $createdAt
     * @return SyncInterface
     */
    public function setCreatedAt(string $createdAt): SyncInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
