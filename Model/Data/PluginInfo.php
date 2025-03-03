<?php
namespace Convertcart\Analytics\Model\Data;

use Convertcart\Analytics\Api\Data\PluginInfoInterface;
use Magento\Framework\DataObject;

class PluginInfo extends DataObject implements PluginInfoInterface
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
        return $this->getData(self::TABLES);
    }

    /**
     * @inheritDoc
     */
    public function setTables(array $tables)
    {
        return $this->setData(self::TABLES, $tables);
    }

    /**
     * @inheritDoc
     */
    public function getTriggers()
    {
        return $this->getData(self::TRIGGERS);
    }

    /**
     * @inheritDoc
     */
    public function setTriggers(array $triggers)
    {
        return $this->setData(self::TRIGGERS, $triggers);
    }
} 