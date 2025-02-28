<?php
namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Api\PluginInfoInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

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
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param ModuleListInterface $moduleList
     */

    public function __construct(
        ResourceConnection $resourceConnection,
        ModuleListInterface $moduleList
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->moduleList = $moduleList;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * Get plugin information.
     *
     * @return \stdClass
     */
    public function getPluginInfo(): \stdClass
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
        $requiredTriggers = ['update_cpe_after_insert_catalog_product_entity_decimal', 'update_cpe_after_update_catalog_product_entity_decimal', 'update_cpe_after_insert_catalog_inventory_stock_item', 'update_cpe_after_update_catalog_inventory_stock_item']; // Replace with your actual trigger names
        $triggersExist = [];
        foreach ($requiredTriggers as $trigger) {
            $query = "SHOW TRIGGERS LIKE '{$trigger}'";
            $triggers = $this->connection->fetchAll($query);
            $triggersExist[$trigger] = !empty($triggers);
        }
        $this->logger->info('Plugin Version ' . json_encode($pluginVersion));
        $this->logger->info('Tables Exist: ' . json_encode($tablesExist));
        $this->logger->info('Triggers Exist: ' . json_encode($triggersExist));
        // Return consolidated information
        return (object) [
            'plugin_version' => $pluginVersion,
            'tables' => $tablesExist,
            'triggers' => $triggersExist,
        ];
    }
}