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
     * @return array<string,bool>
     */
    public function getTables();

    /**
     * Set tables
     *
     * @param array<string,bool> $tables
     * @return $this
     */
    public function setTables($tables);

    /**
     * Get triggers
     *
     * @return array<string,bool>
     */
    public function getTriggers();

    /**
     * Set triggers
     *
     * @param array<string,bool> $triggers
     * @return $this
     */
    public function setTriggers($triggers);
} 