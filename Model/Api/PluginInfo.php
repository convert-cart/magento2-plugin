<?php
namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Api\PluginInfoInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DataObject;
use Convertcart\Analytics\Model\Data\PluginInfoFactory;

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
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param ModuleListInterface $moduleList
     * @param PluginInfoFactory $pluginInfoFactory
     */

    public function __construct(
        ResourceConnection $resourceConnection,
        ModuleListInterface $moduleList,
        \Convertcart\Analytics\Logger\Logger $logger,
        \Convertcart\Analytics\Model\Data\PluginInfoFactory $pluginInfoFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->moduleList = $moduleList;
        $this->connection = $resourceConnection->getConnection();
        $this->logger = $logger;
        $this->pluginInfoFactory = $pluginInfoFactory;
    }

    /**
     * Get plugin information.
     *
     * @return array
     */
    public function getPluginInfo()
    {
        // Get plugin version
        $moduleCode = 'Convertcart_Analytics';
        $moduleInfo = $this->moduleList->getOne($moduleCode);
        $pluginVersion = isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : 'Unknown';

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

        // Use PluginInfoFactory to create a PluginInfo object
        /** @var \Convertcart\Analytics\Model\Data\PluginInfo $pluginInfo */
        $pluginInfo = $this->pluginInfoFactory->create();
        $pluginInfo->setVersion($pluginVersion);
        $pluginInfo->setTables($tablesExist);
        $pluginInfo->setTriggers($triggersExist);

        // Logging for debugging
        $this->logger->debug('Existing triggers: ' . print_r($existingTriggers, true));
        $this->logger->debug('Plugin Info Data: ' . json_encode($pluginInfo));

        return $pluginInfo;
    }
}