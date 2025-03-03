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
     * @return mixed[]
     */
    public function getTables();

    /**
     * Set tables
     *
     * @param mixed[] $tables
     * @return $this
     */
    public function setTables($tables);

    /**
     * Get triggers
     *
     * @return mixed[]
     */
    public function getTriggers();

    /**
     * Set triggers
     *
     * @param mixed[] $triggers
     * @return $this
     */
    public function setTriggers($triggers);
} 