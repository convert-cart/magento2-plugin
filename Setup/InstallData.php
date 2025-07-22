<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Integration\Model\ConfigBasedIntegrationManager;

class InstallData implements InstallDataInterface
{
    private ConfigBasedIntegrationManager $integrationManager;

    public function __construct(
        ConfigBasedIntegrationManager $integrationManager
    ) {
        $this->integrationManager = $integrationManager;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->integrationManager->processIntegrationConfig(['ConvertCart Analytics']);
    }
}
