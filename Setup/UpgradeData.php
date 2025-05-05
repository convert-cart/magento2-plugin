<?php

namespace Vendor\Module\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Trigger on fresh install (when context->getVersion() is NULL)
        if (!$context->getVersion()) {
            $setup->getConnection()->query('SELECT 1');
        }

        // if (version_compare($context->getVersion(), '1.1.0', '<')) {
        //     // Add upgrade logic for version 1.1.0 & above
        // }

        $setup->endSetup();
    }
}
