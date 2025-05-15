<?php
declare(strict_types=1);
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
    /**
     * Get version
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Set version
     *
     * @param string $version
     * @return $this
     */
    /**
     * Set version
     *
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): self;

    /**
     * Get tables
     *
     * @return array<string,bool>
     */
    /**
     * Get tables
     *
     * @return array<string,bool>
     */
    public function getTables(): array;

    /**
     * Set tables
     *
     * @param array<string,bool> $tables
     * @return $this
     */
    /**
     * Set tables
     *
     * @param array<string,bool> $tables
     * @return $this
     */
    public function setTables(array $tables): self;

    /**
     * Get triggers
     *
     * @return array<string,bool>
     */
    /**
     * Get triggers
     *
     * @return array<string,bool>
     */
    public function getTriggers(): array;

    /**
     * Set triggers
     *
     * @param array<string,bool> $triggers
     * @return $this
     */
    /**
     * Set triggers
     *
     * @param array<string,bool> $triggers
     * @return $this
     */
    public function setTriggers(array $triggers): self;

}