<?php

namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;

class Uninstall implements UninstallInterface
{
    /**
     * Drop table and triggers
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws LocalizedException
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $conn = $setup->getConnection();
        $tableName = $setup->getTable('convertcart_sync_activity');

        // Drop the table if it exists
        if ($conn->isTableExists($tableName)) {
            $conn->dropTable($tableName);
        }

        // Array of trigger names to be dropped
        $triggerNames = [
            'update_cpe_after_insert_catalog_product_entity_decimal',
            'update_cpe_after_update_catalog_product_entity_decimal',
            'update_cpe_after_insert_catalog_inventory_stock_item',
            'update_cpe_after_update_catalog_inventory_stock_item'
        ];

        // Loop through each trigger
        foreach ($triggerNames as $triggerName) {
            // Check if the trigger exists
            $triggerExists = $conn->fetchOne(
                "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS 
                 WHERE TRIGGER_NAME = :trigger_name AND TRIGGER_SCHEMA = DATABASE()",
                ['trigger_name' => $triggerName]
            );

            // If the trigger exists, drop it
            if ($triggerExists) {
                try {
                    $conn->query("DROP TRIGGER IF EXISTS $triggerName");
                } catch (\Exception $e) {
                    // Handle exception if trigger dropping fails
                    throw new LocalizedException(__('Error dropping trigger %1: %2', $triggerName, $e->getMessage()));
                }
            }
        }
    }
}
