<?php

namespace ActivePaymentsVendor\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \ActivePaymentsVendor\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}
