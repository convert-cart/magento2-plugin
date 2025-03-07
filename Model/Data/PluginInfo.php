<?php
namespace Convertcart\Analytics\Model\Data;

class PluginInfo implements \JsonSerializable
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
     * @return void
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
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
     * @return array{version: string, tables: string[], triggers: string[]}
     */
    public function jsonSerialize(): array
    {
        // Return only the fields we want, ignoring DataObject's properties
        return array_filter([
            'version' => $this->getVersion(),
            'tables' => $this->getTables(),
            'triggers' => $this->getTriggers()
        ], function($value) {
            return $value !== null;
        });
    }
}
