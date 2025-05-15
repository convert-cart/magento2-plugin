<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Model\Data;

class PluginInfo implements \JsonSerializable
{
    private $ccPluginVersion;
    private $magentoVersion;
    private $tables = [];
    private $triggers = [];

    /**
     * Get Convert Cart plugin version
     *
     * @return string
     */
    public function getCcPluginVersion(): string
    {
        return $this->ccPluginVersion;
    }

    /**
     * Set Convert Cart plugin version
     *
     * @param string $version
     * @return void
     */
    public function setCcPluginVersion(string $version): void
    {
        $this->ccPluginVersion = $version;
    }

    /**
     * Get Magento version
     *
     * @return string
     */
    public function getMagentoVersion(): string
    {
        return $this->magentoVersion;
    }

    /**
     * Set Magento version
     *
     * @param string $version
     * @return void
     */
    public function setMagentoVersion(string $version): void
    {
        $this->magentoVersion = $version;
    }

    /**
     * Get tables
     *
     * @return string[]
     */
    public function getTables(): array
    {
        return array_keys($this->tables);
    }

    /**
     * Set tables
     *
     * @param array<string,bool> $tables
     * @return void
     */
    public function setTables(array $tables): void
    {
        // Only store tables that exist (true values)
        $this->tables = array_filter($tables);
    }

    /**
     * Get triggers
     *
     * @return string[]
     */
    public function getTriggers(): array
    {
        return array_keys($this->triggers);
    }

    /**
     * Set triggers
     *
     * @param array<string,bool> $triggers
     * @return void
     */
    public function setTriggers(array $triggers): void
    {
        // Only store triggers that exist (true values)
        $this->triggers = array_filter($triggers);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array{cc_plugin_version: string, magento_version: string, tables: string[], triggers: string[]}
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'cc_plugin_version' => $this->getCcPluginVersion(),
            'magento_version' => $this->getMagentoVersion(),
            'tables' => $this->getTables(),
            'triggers' => $this->getTriggers()
        ], function($value) {
            return $value !== null;
        });
    }
}
