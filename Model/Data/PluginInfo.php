<?php
namespace Convertcart\Analytics\Model\Data;

use Magento\Framework\DataObject;

class PluginInfo extends DataObject
{
    private $version;
    private $tables;
    private $triggers;

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
     * @return $this
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Get tables
     *
     * @return array<string,bool>
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * Set tables
     *
     * @param array<string,bool> $tables
     * @return $this
     */
    public function setTables(array $tables): void
    {
        $this->tables = $tables;
    }

    /**
     * Get triggers
     *
     * @return array<string,bool>
     */
    public function getTriggers(): array
    {
        return $this->triggers;
    }

    /**
     * Set triggers
     *
     * @param array<string,bool> $triggers
     * @return $this
     */
    public function setTriggers(array $triggers): void
    {
        $this->triggers = $triggers;
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
