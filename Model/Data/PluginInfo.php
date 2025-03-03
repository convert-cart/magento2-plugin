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
    public function setTables($tables)
    {
        if (is_array($tables)) {
            $tables = (object)$tables;
        }
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
    public function setTriggers($triggers)
    {
        if (is_array($triggers)) {
            $triggers = (object)$triggers;
        }
        return $this->setData(self::TRIGGERS, $triggers);
    }

    /**
     * Convert object data to array
     *
     * @return array
     */
    public function getData($key = '', $index = null)
    {
        $data = parent::getData($key);
        if ($key === self::TABLES || $key === self::TRIGGERS) {
            return (array)$data;
        }
        return $data;
    }
} 