<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Api\PluginInfoInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DataObject;
use Convertcart\Analytics\Model\Data\PluginInfoFactory;
use Convertcart\Analytics\Logger\Logger;
use Magento\Framework\App\ProductMetadataInterface;

class PluginInfo implements PluginInfoInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var \Convertcart\Analytics\Logger\Logger
     */
    protected $logger;

    /**
     * @var PluginInfoFactory
     */
    protected $pluginInfoFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param ModuleListInterface $moduleList
     * @param Logger $logger
     * @param PluginInfoFactory $pluginInfoFactory
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ModuleListInterface $moduleList,
        Logger $logger,
        PluginInfoFactory $pluginInfoFactory,
        ProductMetadataInterface $productMetadata
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->moduleList = $moduleList;
        $this->connection = $resourceConnection->getConnection();
        $this->logger = $logger;
        $this->pluginInfoFactory = $pluginInfoFactory;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Get plugin information.
     *
     * @return \Convertcart\Analytics\Model\Data\PluginInfo
     */
    public function getPluginInfo(): \Convertcart\Analytics\Model\Data\PluginInfo
    {
        // Get Convert Cart plugin version
        $moduleCode = 'Convertcart_Analytics';
        $moduleInfo = $this->moduleList->getOne($moduleCode);
        $pluginVersion = isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : 'Unknown';

        // Get Magento version
        $magentoVersion = $this->productMetadata->getVersion();

        // Check if required tables exist
        $requiredTables = ['convertcart_sync_activity'];
        $existingTables = $this->connection->listTables();

        // Create associative array for tables
        $tablesExist = [];
        foreach ($requiredTables as $table) {
            $tableName = $this->resourceConnection->getTableName($table);
            $tablesExist[$table] = in_array($tableName, $existingTables);
        }

        // Check if required triggers exist
        $requiredTriggers = [
            'update_cpe_after_insert_catalog_product_entity_decimal',
            'update_cpe_after_update_catalog_product_entity_decimal',
            'update_cpe_after_insert_catalog_inventory_stock_item',
            'update_cpe_after_update_catalog_inventory_stock_item'
        ];

        $query = "SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()";
        $existingTriggers = $this->connection->fetchCol($query);

        // Create associative array for triggers
        $triggersExist = [];
        foreach ($requiredTriggers as $trigger) {
            $triggersExist[$trigger] = in_array($trigger, $existingTriggers);
        }

        /** @var \Convertcart\Analytics\Model\Data\PluginInfo $data */
        $data = $this->pluginInfoFactory->create();
        $data->setCcPluginVersion($pluginVersion);
        $data->setMagentoVersion($magentoVersion);
        $data->setTables($tablesExist);
        $data->setTriggers($triggersExist);

        // Logging for debugging
        $this->logger->debug('Existing triggers: ' . print_r($existingTriggers, true));
        $this->logger->debug('Plugin Info Data: ' . json_encode($data));

        return $data;
    }
}
