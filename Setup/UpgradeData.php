<?php

namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Upgrade data for the module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.0', '<')) {
            $setup->getConnection()->insert(
                $setup->getTable('core_config_data'),
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'convertcart_analytics/new_setting',
                    'value' => '1'
                ]
            );
        }

        $setup->endSetup();
    }
}
