<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model;

use Magento\Framework\Model\AbstractModel;

class Sync extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Sync::class);
    }
}
