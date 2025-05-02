<?php

namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;

class InstallSchema implements InstallSchemaInterface
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

        if (!$conn->isTableExists($tableName)) {
            $table = $conn->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'auto_increment' => true, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Item ID'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    55,
                    ['nullable' => false],
                    'Type'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('Convertcart Sync Activity Table')
                ->setOption('charset', 'utf8');
            
            $conn->createTable($table);
        }

        // 🔹 Create triggers safely
        $triggers = [
            'update_cpe_after_insert_catalog_product_entity_decimal' => "
                CREATE TRIGGER IF NOT EXISTS update_cpe_after_insert_catalog_product_entity_decimal
                AFTER INSERT ON {$setup->getTable('catalog_product_entity_decimal')}
                FOR EACH ROW
                BEGIN
                    UPDATE {$setup->getTable('catalog_product_entity')}
                    SET updated_at = NOW()
                    WHERE entity_id = NEW.entity_id;
                END;
            ",
            'update_cpe_after_update_catalog_product_entity_decimal' => "
                CREATE TRIGGER IF NOT EXISTS update_cpe_after_update_catalog_product_entity_decimal
                AFTER UPDATE ON {$setup->getTable('catalog_product_entity_decimal')}
                FOR EACH ROW
                BEGIN
                    UPDATE {$setup->getTable('catalog_product_entity')}
                    SET updated_at = NOW()
                    WHERE entity_id = NEW.entity_id;
                END;
            "
        ];

        foreach ($triggers as $triggerName => $triggerSql) {
            try {
                $conn->query($triggerSql);
            } catch (\Exception $e) {
                throw new \RuntimeException("Error creating trigger $triggerName: " . $e->getMessage());
            }
        }

        $setup->endSetup();
    }
}
