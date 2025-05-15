<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model\ResourceModel\Sync;

use Convertcart\Analytics\Model\Sync;
use Convertcart\Analytics\Model\ResourceModel\Sync as SyncResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Collection initialization.
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Sync::class, SyncResourceModel::class);
    }
}
