<?php

namespace ActivePaymentsVendor\WPDesk\PluginBuilder\BuildDirector;

use ActivePaymentsVendor\WPDesk\PluginBuilder\Builder\AbstractBuilder;
use ActivePaymentsVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use ActivePaymentsVendor\WPDesk\PluginBuilder\Storage\StorageFactory;
class LegacyBuildDirector
{
    /** @var AbstractBuilder */
    private $builder;
    public function __construct(\ActivePaymentsVendor\WPDesk\PluginBuilder\Builder\AbstractBuilder $builder)
    {
        $this->builder = $builder;
    }
    /**
     * Builds plugin
     */
    public function build_plugin()
    {
        $this->builder->build_plugin();
        $this->builder->init_plugin();
        $storage = new \ActivePaymentsVendor\WPDesk\PluginBuilder\Storage\StorageFactory();
        $this->builder->store_plugin($storage->create_storage());
    }
    /**
     * Returns built plugin
     *
     * @return AbstractPlugin
     */
    public function get_plugin()
    {
        return $this->builder->get_plugin();
    }
}
