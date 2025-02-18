<?php
namespace Convertcart\Analytics\Model\Api;

use Convertcart\Analytics\Api\PluginInfoInterface;
use Magento\Framework\App\ResourceConnection;

class PluginInfo implements PluginInfoInterface
{
    protected $resourceConnection;
    protected $moduleList;

    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->moduleList = $moduleList;
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
        $requiredTables = ['convertcart_sync_activity']; // Replace with your actual table names
        $connection = $this->resourceConnection->getConnection();
        $existingTables = $connection->getTables();

        $tablesExist = [];
        foreach ($requiredTables as $table) {
            $tablesExist[$table] = in_array($this->resourceConnection->getTableName($table), $existingTables);
        }

        // Check if required triggers exist
        $requiredTriggers = ['update_cpe_after_insert_catalog_product_entity_decimal', 'update_cpe_after_update_catalog_product_entity_decimal', 'update_cpe_after_insert_catalog_inventory_stock_item', 'update_cpe_after_update_catalog_inventory_stock_item']; // Replace with your actual trigger names
        $triggersExist = [];
        foreach ($requiredTriggers as $trigger) {
            $query = "SHOW TRIGGERS LIKE '{$trigger}'";
            $triggers = $connection->fetchAll($query);
            $triggersExist[$trigger] = !empty($triggers);
        }

        // Return consolidated information
        return [
            'plugin_version' => $pluginVersion,
            'tables_exist' => $tablesExist,
            'triggers_exist' => $triggersExist,
        ];
    }
}