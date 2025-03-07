<?php
namespace Convertcart\Analytics\Model\Data;

use Magento\Framework\DataObject;

class PluginInfo extends DataObject
{
    private $version;
    private $tables;
    private $triggers;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function setTables(array $tables): void
    {
        $this->tables = $tables;
    }

    public function getTriggers(): array
    {
        return $this->triggers;
    }

    public function setTriggers(array $triggers): void
    {
        $this->triggers = $triggers;
    }

    public function jsonSerialize(): array
    {
        return [
            'version' => $this->getVersion(),
            'tables' => $this->getTables(),
            'triggers' => $this->getTriggers()
        ];
    }
}
