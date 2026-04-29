<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * Upgrades the database schema for the Convertcart Analytics module.
     *
     * @param SchemaSetupInterface   $setup   Schema setup
     * @param ModuleContextInterface $context Module context
     *
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $conn = $setup->getConnection();
        if ($setup->getConnection()->isTableExists('convertcart_sync_activity') != true) {
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
        }

        if (version_compare($context->getVersion(), '1.0.19', '<')) {
            $triggers = [
                'update_cpe_after_insert_inventory_source_item' => "
                    CREATE TRIGGER update_cpe_after_insert_inventory_source_item
                    AFTER INSERT ON " . $setup->getTable('inventory_source_item') . "
                    FOR EACH ROW
                    BEGIN
                        UPDATE " . $setup->getTable('catalog_product_entity') . " AS parent
                        INNER JOIN " . $setup->getTable('catalog_product_relation') . " AS rel ON rel.parent_id = parent.entity_id
                        INNER JOIN " . $setup->getTable('catalog_product_entity') . " AS child ON child.entity_id = rel.child_id
                        SET parent.updated_at = NOW()
                        WHERE child.sku = NEW.sku;
                        UPDATE " . $setup->getTable('catalog_product_entity') . "
                        SET updated_at = NOW()
                        WHERE sku = NEW.sku;
                    END",
                'update_cpe_after_update_inventory_source_item' => "
                    CREATE TRIGGER update_cpe_after_update_inventory_source_item
                    AFTER UPDATE ON " . $setup->getTable('inventory_source_item') . "
                    FOR EACH ROW
                    BEGIN
                        UPDATE " . $setup->getTable('catalog_product_entity') . " AS parent
                        INNER JOIN " . $setup->getTable('catalog_product_relation') . " AS rel ON rel.parent_id = parent.entity_id
                        INNER JOIN " . $setup->getTable('catalog_product_entity') . " AS child ON child.entity_id = rel.child_id
                        SET parent.updated_at = NOW()
                        WHERE child.sku = NEW.sku;
                        UPDATE " . $setup->getTable('catalog_product_entity') . "
                        SET updated_at = NOW()
                        WHERE sku = NEW.sku;
                    END",
                'update_cpe_after_insert_inventory_reservation' => "
                    CREATE TRIGGER update_cpe_after_insert_inventory_reservation
                    AFTER INSERT ON " . $setup->getTable('inventory_reservation') . "
                    FOR EACH ROW
                    BEGIN
                        UPDATE " . $setup->getTable('catalog_product_entity') . " AS parent
                        INNER JOIN " . $setup->getTable('catalog_product_relation') . " AS rel ON rel.parent_id = parent.entity_id
                        INNER JOIN " . $setup->getTable('catalog_product_entity') . " AS child ON child.entity_id = rel.child_id
                        SET parent.updated_at = NOW()
                        WHERE child.sku = NEW.sku;
                        UPDATE " . $setup->getTable('catalog_product_entity') . "
                        SET updated_at = NOW()
                        WHERE sku = NEW.sku;
                    END"
            ];
            foreach ($triggers as $triggerName => $triggerSql) {
                $triggerExists = $conn->fetchOne(
                    "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS
                     WHERE TRIGGER_NAME = :trigger_name AND TRIGGER_SCHEMA = DATABASE()",
                    ['trigger_name' => $triggerName]
                );
                if (!$triggerExists) {
                    $conn->getConnection()->exec($triggerSql);
                }
            }
        }

        $setup->endSetup();
    }
}
