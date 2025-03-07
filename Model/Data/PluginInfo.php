<?php
namespace Convertcart\Analytics\Model\Data;

use Convertcart\Analytics\Api\Data\PluginInfoInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class PluginInfo extends AbstractExtensibleModel implements PluginInfoInterface, \JsonSerializable
{
    /**
     * @inheritDoc
     */
    public function getVersion()
    {
        return $this->getData(self::VERSION);
    }

    /**
     * @inheritDoc
     */
    public function setVersion($version)
    {
        return $this->setData(self::VERSION, $version);
    }

    /**
     * @inheritDoc
     */
    public function getTables()
    {
        $tables = $this->getData(self::TABLES);
        return is_array($tables) ? $tables : [];
    }

    /**
     * @inheritDoc
     */
    public function setTables($tables)
    {
        return $this->setData(self::TABLES, $tables);
    }

    /**
     * @inheritDoc
     */
    public function getTriggers()
    {
        $triggers = $this->getData(self::TRIGGERS);
        return is_array($triggers) ? $triggers : [];
    }

    /**
     * @inheritDoc
     */
    public function setTriggers($triggers)
    {
        return $this->setData(self::TRIGGERS, $triggers);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'version' => $this->getVersion(),
            'tables' => (object)$this->getTables(), // Cast to object to force JSON object notation
            'triggers' => (object)$this->getTriggers() // Cast to object to force JSON object notation
        ];
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Convertcart\Analytics\Model\ResourceModel\PluginInfo::class);
    }
} 