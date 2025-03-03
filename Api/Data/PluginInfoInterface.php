<?php
namespace Convertcart\Analytics\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface PluginInfoInterface extends ExtensibleDataInterface
{
    const VERSION = 'version';
    const TABLES = 'tables';
    const TRIGGERS = 'triggers';

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion();

    /**
     * Set version
     *
     * @param string $version
     * @return $this
     */
    public function setVersion($version);

    /**
     * Get tables
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface[]|array
     */
    public function getTables();

    /**
     * Set tables
     *
     * @param array $tables
     * @return $this
     */
    public function setTables($tables);

    /**
     * Get triggers
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface[]|array
     */
    public function getTriggers();

    /**
     * Set triggers
     *
     * @param array $triggers
     * @return $this
     */
    public function setTriggers($triggers);
} 