<?php
namespace Convertcart\Analytics\Api;

interface PluginInfoInterface
{
    /**
     * Get plugin information.
     *
     * @api
     * @return \Convertcart\Analytics\Api\Data\PluginInfoInterface
     */
    public function getPluginInfo();
}