<?php
namespace Convertcart\Analytics\Api;

interface PluginInfoInterface
{
    /**
     * Get plugin information.
     *
     * @return \stdClass
     */
    public function getPluginInfo();
}