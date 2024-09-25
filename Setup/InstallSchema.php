<?php

namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException; // Import LocalizedException

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * Create table and triggers
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $conn = $setup->getConnection();
        $tableName = $setup->getTable('convertcart_sync_activity');

        // Check if the table already exists
        if (!$conn->isTableExists($tableName)) {
            // Create the new table
            $table = $conn->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'auto_increment' => true, 'primary' => true]
                )
                ->addColumn(
                    'item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    55,
                    ['nullable' => false]
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
                )
                ->setOption('charset', 'utf8mb4'); // Use utf8mb4 for better compatibility
            $conn->createTable($table);
        }

        $triggers = [
            'update_cpe_after_insert_catalog_product_entity_decimal' => "
                CREATE TRIGGER update_cpe_after_insert_catalog_product_entity_decimal
                AFTER INSERT ON " . $setup->getTable('catalog_product_entity_decimal') . "
                FOR EACH ROW
                BEGIN
                    UPDATE " . $setup->getTable('catalog_product_entity') . "
                    SET updated_at = NOW()
                    WHERE entity_id = NEW.entity_id;
                END;",
            'update_cpe_after_update_catalog_product_entity_decimal' => "
                CREATE TRIGGER update_cpe_after_update_catalog_product_entity_decimal
                AFTER UPDATE ON " . $setup->getTable('catalog_product_entity_decimal') . "
                FOR EACH ROW
                BEGIN
                    UPDATE " . $setup->getTable('catalog_product_entity') . "
                    SET updated_at = NOW()
                    WHERE entity_id = NEW.entity_id;
                END;",
            'update_cpe_after_insert_catalog_inventory_stock_item' => "
                CREATE TRIGGER update_cpe_after_insert_catalog_inventory_stock_item
                AFTER INSERT ON " . $setup->getTable('cataloginventory_stock_item') . "
                FOR EACH ROW
                BEGIN
                    UPDATE " . $setup->getTable('catalog_product_entity') . "
                    SET updated_at = NOW()
                    WHERE entity_id = NEW.product_id;
                END;",
            'update_cpe_after_update_catalog_inventory_stock_item' => "
                CREATE TRIGGER update_cpe_after_update_catalog_inventory_stock_item
                AFTER UPDATE ON " . $setup->getTable('cataloginventory_stock_item') . "
                FOR EACH ROW
                BEGIN
                    UPDATE " . $setup->getTable('catalog_product_entity') . "
                    SET updated_at = NOW()
                    WHERE entity_id = NEW.product_id;
                END;"
        ];
        
        // Loop through each trigger
        foreach ($triggers as $triggerName => $triggerSql) {
            // Check if the trigger already exists
            $triggerExists = $conn->fetchOne(
                "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS 
                 WHERE TRIGGER_NAME = :trigger_name AND TRIGGER_SCHEMA = DATABASE()",
                ['trigger_name' => $triggerName]
            );
        
            // If the trigger does not exist, create it
            if (!$triggerExists) {
                try {
                    $conn->query($triggerSql);
                } catch (\Exception $e) {
                    // Handle exception if trigger creation fails
                    throw new LocalizedException(__('Error creating trigger %1: %2', $triggerName, $e->getMessage()));
                }
            }
        }
        
        $setup->endSetup(); // Finalize the setup process
    }
}