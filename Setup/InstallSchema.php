<?php

namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

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

        if ($conn->isTableExists($tableName) != true) {
            $table = $conn->newTable($tableName)
                        ->addColumn(
                            'id',
                            Table::TYPE_INTEGER,
                            null,
                            ['unsigned'=>true,'nullable'=>false,'auto_increment' => true,'primary'=>true]
                        )
                        ->addColumn(
                            'item_id',
                            Table::TYPE_INTEGER,
                            null,
                            ['nullable'=>false]
                        )
                        ->addColumn(
                            'type',
                            Table::TYPE_TEXT,
                            55,
                            ['nullable'=>false]
                        )
                        ->addColumn(
                            'created_at',
                            Table::TYPE_TIMESTAMP,
                            null,
                            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
                        )
                        ->setOption('charset', 'utf8');
            $conn->createTable($table);
        }

        // Array of trigger names
        $triggerNames = [
            'update_cpe_after_insert_catalog_product_entity_decimal',
            'update_cpe_after_update_catalog_product_entity_decimal',
            'update_cpe_after_insert_catalog_inventory_stock_item',
            'update_cpe_after_update_catalog_inventory_stock_item'
        ];

        // Corresponding SQL to create each trigger
        $triggers = [
            "CREATE TRIGGER update_cpe_after_insert_catalog_product_entity_decimal
             AFTER INSERT ON catalog_product_entity_decimal
             FOR EACH ROW
             BEGIN
             UPDATE catalog_product_entity
             SET updated_at = NOW()
             WHERE entity_id = NEW.entity_id;
             END;",
             
            "CREATE TRIGGER update_cpe_after_update_catalog_product_entity_decimal
             AFTER UPDATE ON catalog_product_entity_decimal
             FOR EACH ROW
             BEGIN
             UPDATE catalog_product_entity
             SET updated_at = NOW()
             WHERE entity_id = NEW.entity_id;
             END;",
             
            "CREATE TRIGGER update_cpe_after_insert_catalog_inventory_stock_item
             AFTER INSERT ON cataloginventory_stock_item
             FOR EACH ROW
             BEGIN
             UPDATE catalog_product_entity
             SET updated_at = NOW()
             WHERE entity_id = NEW.item_id;
             END;",
             
            "CREATE TRIGGER update_cpe_after_update_catalog_inventory_stock_item
             AFTER UPDATE ON cataloginventory_stock_item
             FOR EACH ROW
             BEGIN
             UPDATE catalog_product_entity
             SET updated_at = NOW()
             WHERE entity_id = NEW.item_id;
             END;"
        ];

        // Loop through each trigger
        foreach ($triggerNames as $index => $triggerName) {
            // Check if the trigger already exists
            $triggerExists = $conn->fetchOne(
                "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS 
                 WHERE TRIGGER_NAME = :trigger_name AND TRIGGER_SCHEMA = DATABASE()",
                ['trigger_name' => $triggerName]
            );

            // If the trigger does not exist, create it
            if (!$triggerExists) {
                try {
                    $conn->query($triggers[$index]);
                } catch (\Exception $e) {
                    // Handle exception if trigger creation fails
                    throw new \RuntimeException('Error creating trigger: ' . $e->getMessage());
                }
            }
        }

        $setup->endSetup();
    }
}
