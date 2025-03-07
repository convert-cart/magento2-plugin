<?php
namespace Convertcart\Analytics\Api;

interface PluginInfoInterface
{
    /**
     * Get plugin information.
     *
     * @api
     * @return \Convertcart\Analytics\Model\Data\PluginInfo
     */
    public function getPluginInfo();
}