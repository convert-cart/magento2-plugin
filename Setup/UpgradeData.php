<?php 

namespace Vendor\Module\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Psr\Log\LoggerInterface;

class UpgradeData implements UpgradeDataInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $fromVersion = $context->getVersion() ?? '0.0.0';
        $this->logger->info("Vendor_Module UpgradeData running. From version: $fromVersion");

        if (!$context->getVersion()) {
            $this->logger->info("Fresh install detected for Vendor_Module.");
            // Optional test query
            $setup->getConnection()->query('SELECT 1');
            $this->logger->info("Fresh install triggered for Vendor_Module.");
        }

        if (version_compare($fromVersion, '1.1.0', '<')) {
            $this->logger->info("Applying updates for version 1.1.0...");
            // Upgrade logic here
        }

        $setup->endSetup();
    }
}
