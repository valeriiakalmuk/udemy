<?php

namespace WGPayuVendor\WPDesk\WP\Plugin;

/**
 * Some utility methods that we don't know where to put them
 */
class Utils
{
    /**
     * Checks if plugin is active
     *
     * @param string $plugin_file
     * @return bool
     */
    public function is_plugin_active($plugin_file)
    {
        $active_plugins = (array) \get_option('active_plugins', array());
        if (\is_multisite()) {
            $active_plugins = \array_merge($active_plugins, \get_site_option('active_sitewide_plugins', array()));
        }
        return \in_array($plugin_file, $active_plugins) || \array_key_exists($plugin_file, $active_plugins);
    }
}
