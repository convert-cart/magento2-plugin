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

        // Create associative array with explicit keys
        $tablesExist = [];
        foreach ($requiredTables as $table) {
            $tableName = $this->resourceConnection->getTableName($table);
            // Use the table name as the key
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

        // Create associative array with explicit keys
        $triggersExist = [];
        foreach ($requiredTriggers as $trigger) {
            // Use the trigger name as the key
            $triggersExist[$trigger] = in_array($trigger, $existingTriggers);
        }

        // Create a simple array structure that will be properly serialized
        $result = [
            'version' => $pluginVersion,
            'tables' => (object)$tablesExist,
            'triggers' => (object)$triggersExist
        ];

        // Logging for debugging
        $this->logger->debug('existing trigger: ' . print_r($existingTriggers, true));
        $this->logger->debug('Plugin Info Data: ' . print_r($result, true));

        return $result;
    }
}