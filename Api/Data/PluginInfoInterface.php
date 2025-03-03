<?php
namespace Convertcart\Analytics\Api\Data;

interface PluginInfoInterface
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
     * @return \Convertcart\Analytics\Api\Data\TableStatusInterface[]
     */
    public function getTables();

    /**
     * Set tables
     *
     * @param \Convertcart\Analytics\Api\Data\TableStatusInterface[] $tables
     * @return $this
     */
    public function setTables(array $tables);

    /**
     * Get triggers
     *
     * @return \Convertcart\Analytics\Api\Data\TriggerStatusInterface[]
     */
    public function getTriggers();

    /**
     * Set triggers
     *
     * @param \Convertcart\Analytics\Api\Data\TriggerStatusInterface[] $triggers
     * @return $this
     */
    public function setTriggers(array $triggers);
} 