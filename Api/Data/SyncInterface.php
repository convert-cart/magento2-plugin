<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convertcart\Analytics\Api\Data;

/**
 * Interface SyncInterface
 *
 * @api
 * @since 100.0.2
 */
interface SyncInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * #@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ID = 'id';
    public const ITEM_ID = 'item_id';
    public const TYPE   = 'type';
    public const CREATED_AT = 'created_at';
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
    public function getId(): ?int;

    /**
     * Set ID.
     *
     * @param integer $id
     *
     * @return $this
     */
    /**
     * Set ID.
     *
     * @param  int $id
     * @return self
     */
    public function setId(int $id): self;

    /**
     * Get type.
     *
     * @return string
     */
    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType(): ?string;
 
    /**
     * Set type.
     *
     * @param string $type
     *
     * @return $this
     */
    /**
     * Set type.
     *
     * @param  string $type
     * @return self
     */
    public function setType(string $type): self;

    /**
     * Get itemid.
     *
     * @return string
     */
    /**
     * Get itemId.
     *
     * @return string|null
     */
    public function getItemId(): ?string;
 
    /**
     * Set itemId.
     *
     * @param string $itemId
     *
     * @return $this
     */
    /**
     * Set itemId.
     *
     * @param  string $itemId
     * @return self
     */
    public function setItemId(string $itemId): self;
  
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
    public function getCreatedAt(): ?string;
 
    /**
     * Set createdAt.
     *
     * @param string $createdAt
     *
     * @return $this
     */
    /**
     * Set createdAt.
     *
     * @param  string $createdAt
     * @return self
     */
    public function setCreatedAt(string $createdAt): self;
}
