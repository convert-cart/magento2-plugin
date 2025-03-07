<?php
namespace Convertcart\Analytics\Api;

interface PluginInfoInterface
{
    /**
     * Get plugin information.
     *
     * @api
     * @return array
     */
    public function getPluginInfo();
}