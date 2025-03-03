<?php
namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Api\PluginInfoInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use stdClass;

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
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param ModuleListInterface $moduleList
     */

    public function __construct(
        ResourceConnection $resourceConnection,
        ModuleListInterface $moduleList,
        \Convertcart\Analytics\Logger\Logger $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->moduleList = $moduleList;
        $this->connection = $resourceConnection->getConnection();
        $this->logger = $logger;
    }

    /**
     * Get plugin information.
     *
     * @return \stdClass
     */
    public function getPluginInfo()
    {
        // Get plugin version
        $moduleCode = 'Convertcart_Analytics';
        $moduleInfo = $this->moduleList->getOne($moduleCode);
        $pluginVersion = isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : 'Unknown';

        // Check if required tables exist
        $requiredTables = ['convertcart_sync_activity']; // Replace with your actual table names
        $existingTables = $this->connection->listTables();

        $tablesExist = [];
        foreach ($requiredTables as $table) {
            $tableName = $this->resourceConnection->getTableName($table);
            $tablesExist[$table] = in_array($tableName, $existingTables);
        }

        // Check if required triggers exist
        $requiredTriggers = ['update_cpe_after_insert_catalog_product_entity_decimal', 'update_cpe_after_update_catalog_product_entity_decimal', 'update_cpe_after_insert_catalog_inventory_stock_item', 'update_cpe_after_update_catalog_inventory_stock_item'];
        $triggersExist = [];

        $query = "SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()";
        $existingTriggers = $this->connection->fetchCol($query);

        foreach ($requiredTriggers as $trigger) {
            $triggersExist[$trigger] = in_array($trigger, $existingTriggers);
        }

        // Return consolidated information
        $data = new stdClass();

        $data->plugin_version = $pluginVersion;
        $data->tables = $tablesExist;
        $data->triggers = $triggersExist;

        header('Content-Type: application/json');
        $obj = new stdClass();
        $obj->name = "John";
        $obj->age = 30;
        $obj->city = "New York";
        $json = json_encode($obj);

        $this->logger->debug('existing trigger: ' . print_r($existingTriggers, true));
        $this->logger->debug('Plugin Info Data: ' . print_r($data, true));

        // Return the array directly instead of json_decode(json_encode())
        echo $json;

    }
}