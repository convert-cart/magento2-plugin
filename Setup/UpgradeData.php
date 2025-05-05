<?php

namespace Vendor\Module\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/* 
    No data updates, 
    but the presence of this file ensures 
    Magento updates data_version of setup_module table
*/

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $setup->endSetup();
    }
}
