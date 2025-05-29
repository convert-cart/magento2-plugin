<?php
declare(strict_types=1);
namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Psr\Log\LoggerInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * InstallSchema constructor.
     *
     * @param LoggerInterface $logger Logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }
    /**
     * Create table and triggers
     *
     * @param SchemaSetupInterface $setup Schema setup
     * @param ModuleContextInterface $context Module context
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        try {
            $conn = $setup->getConnection();
            $tableName = $setup->getTable('convertcart_sync_activity');
            if (!$conn->isTableExists($tableName)) {
                $this->logger->info("Convertcart_Analytics: Creating table: {$tableName}");
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
                $this->logger->info("Convertcart_Analytics: Table created successfully.");
            } else {
                $this->logger->info("Convertcart_Analytics: Table already exists.");
            }

            $triggers = [
                'update_cpe_after_insert_catalog_product_entity_decimal' => "
                    CREATE TRIGGER update_cpe_after_insert_catalog_product_entity_decimal
                    AFTER INSERT ON " . $setup->getTable('catalog_product_entity_decimal') . "
                    FOR EACH ROW
                        UPDATE " . $setup->getTable('catalog_product_entity') . "
                        SET updated_at = NOW()
                        WHERE entity_id = NEW.entity_id;",
                'update_cpe_after_update_catalog_product_entity_decimal' => "
                    CREATE TRIGGER update_cpe_after_update_catalog_product_entity_decimal
                    AFTER UPDATE ON " . $setup->getTable('catalog_product_entity_decimal') . "
                    FOR EACH ROW
                        UPDATE " . $setup->getTable('catalog_product_entity') . "
                        SET updated_at = NOW()
                        WHERE entity_id = NEW.entity_id;",
                'update_cpe_after_insert_catalog_inventory_stock_item' => "
                    CREATE TRIGGER update_cpe_after_insert_catalog_inventory_stock_item
                    AFTER INSERT ON " . $setup->getTable('cataloginventory_stock_item') . "
                    FOR EACH ROW
                        UPDATE " . $setup->getTable('catalog_product_entity') . "
                        SET updated_at = NOW()
                        WHERE entity_id = NEW.product_id;",
                'update_cpe_after_update_catalog_inventory_stock_item' => "
                    CREATE TRIGGER update_cpe_after_update_catalog_inventory_stock_item
                    AFTER UPDATE ON " . $setup->getTable('cataloginventory_stock_item') . "
                    FOR EACH ROW
                        UPDATE " . $setup->getTable('catalog_product_entity') . "
                        SET updated_at = NOW()
                        WHERE entity_id = NEW.product_id;"
            ];

            foreach ($triggers as $triggerName => $triggerSql) {
                $triggerExists = $conn->fetchOne(
                    "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS 
                     WHERE TRIGGER_NAME = :trigger_name AND TRIGGER_SCHEMA = DATABASE()",
                    ['trigger_name' => $triggerName]
                );
                if (!$triggerExists) {
                    try {
                        $conn->query($triggerSql);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
            $setup->endSetup();
        } catch (\Exception $e) {
            $this->logger->error('Convertcart_Analytics: Error during schema install - ' . $e->getMessage());
            throw $e;
        }
    }
}
