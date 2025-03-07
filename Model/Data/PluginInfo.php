<?php
namespace Convertcart\Analytics\Model\Data;

use Magento\Framework\DataObject;

class PluginInfo extends DataObject implements \JsonSerializable
{
    private $version;
    private $tables = [];
    private $triggers = [];

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Get tables
     *
     * @return array
     */
    public function getTables(): array
    {
        return array_keys($this->tables);
    }

    /**
     * Set tables
     *
     * @param array $tables
     */
    public function setTables(array $tables): void
    {
        // Only store tables that exist (true values)
        $this->tables = array_filter($tables);
    }

    /**
     * Get triggers
     *
     * @return array
     */
    public function getTriggers(): array
    {
        return array_keys($this->triggers);
    }

    /**
     * Set triggers
     *
     * @param array $triggers
     */
    public function setTriggers(array $triggers): void
    {
        // Only store triggers that exist (true values)
        $this->triggers = array_filter($triggers);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'version' => $this->getVersion(),
            'tables' => $this->getTables(),
            'triggers' => $this->getTriggers()
        ];
    }
}
