<?php
namespace Convertcart\Analytics\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PluginInfo extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        // Define a dummy table and id field since we're not actually using the database
        $this->_init('dummy_table', 'entity_id');
    }
} 