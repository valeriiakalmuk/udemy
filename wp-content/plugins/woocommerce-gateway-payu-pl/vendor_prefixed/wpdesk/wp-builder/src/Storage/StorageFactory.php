<?php

namespace WGPayuVendor\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \WGPayuVendor\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}
