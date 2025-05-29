<?php
declare(strict_types=1);
namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * Constructor for logging
     */
    /**
     * Constructor for Uninstall class.
     *
     * @param LoggerInterface $logger Logger instance
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Drop table, remove triggers, and clean database
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws LocalizedException
     */
    /**
     * Drop table, remove triggers, and clean database.
     *
     * @param SchemaSetupInterface   $setup   Schema setup
     * @param ModuleContextInterface $context Module context
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->logger->info('Starting Convertcart_Analytics Uninstall...');
            $setup->startSetup();
            $conn = $setup->getConnection();

            $tableName = $setup->getTable('convertcart_sync_activity');
            if ($conn->isTableExists($tableName)) {
                $this->logger->info("Dropping table: $tableName");
                $conn->dropTable($tableName);
            }

            $triggerNames = [
                'update_cpe_after_insert_catalog_product_entity_decimal',
                'update_cpe_after_update_catalog_product_entity_decimal',
                'update_cpe_after_insert_catalog_inventory_stock_item',
                'update_cpe_after_update_catalog_inventory_stock_item'
            ];
            foreach ($triggerNames as $triggerName) {
                $triggerExists = $conn->fetchOne(
                    "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS 
                 WHERE TRIGGER_NAME = :trigger_name AND TRIGGER_SCHEMA = DATABASE()",
                    ['trigger_name' => $triggerName]
                );
                if ($triggerExists) {
                    try {
                        $this->logger->info("Dropping trigger: $triggerName");
                        // @codingStandardsIgnoreLine
                        $conn->query("DROP TRIGGER IF EXISTS $triggerName");
                    } catch (\Exception $e) {
                        $this->logger->error("Error dropping trigger $triggerName: " . $e->getMessage());
                    }
                }
            }

            $this->logger->info('Removing module entry from setup_module...');
            $conn->delete($setup->getTable('setup_module'), ['module = ?' => 'Convertcart_Analytics']);

            $this->logger->info('Removing module configurations from core_config_data...');
            $conn->delete($setup->getTable('core_config_data'), ['path LIKE ?' => 'convertcart_analytics/%']);
            $setup->endSetup();
            $this->logger->info('Convertcart_Analytics Uninstall completed successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Convertcart_Analytics: Uninstall failed: ' . $e->getMessage());
            throw new LocalizedException(__('Convertcart_Analytics: Uninstall failed'));
        }
    }
}
