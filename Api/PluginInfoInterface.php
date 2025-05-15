<?php
declare(strict_types=1);
namespace Convertcart\Analytics\Api;

interface PluginInfoInterface
{
    /**
     * Get plugin information.
     *
     * @api
     * @return \Convertcart\Analytics\Model\Data\PluginInfo
     */
    /**
     * Get plugin information.
     *
     * @api
     * @return \Convertcart\Analytics\Model\Data\PluginInfo
     */
    public function getPluginInfo(): \Convertcart\Analytics\Model\Data\PluginInfo;
}