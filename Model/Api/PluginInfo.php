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
     * @param ResourceConnection       $resourceConnection
     * @param ModuleListInterface      $moduleList
     * @param Logger                   $logger
     * @param PluginInfoFactory        $pluginInfoFactory
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
        $moduleCode = 'Convertcart_Analytics';
        $moduleInfo = $this->moduleList->getOne($moduleCode);
        $pluginVersion = isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : 'Unknown';

        $magentoVersion = $this->productMetadata->getVersion();

        $requiredTables = ['convertcart_sync_activity'];
        $existingTables = $this->connection->listTables();

        $tablesExist = [];
        foreach ($requiredTables as $table) {
            $tableName = $this->resourceConnection->getTableName($table);
            $tablesExist[$table] = in_array($tableName, $existingTables);
        }

        $triggersExist = [];

        $data = $this->pluginInfoFactory->create();
        $data->setCcPluginVersion($pluginVersion);
        $data->setMagentoVersion($magentoVersion);
        $data->setTables($tablesExist);
        $data->setTriggers($triggersExist);

        return $data;
    }
}
