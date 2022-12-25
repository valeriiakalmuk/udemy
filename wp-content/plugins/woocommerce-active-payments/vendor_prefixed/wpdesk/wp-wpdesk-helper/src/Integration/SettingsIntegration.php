<?php

namespace ActivePaymentsVendor\WPDesk\Helper\Integration;

use ActivePaymentsVendor\WPDesk\Helper\Page\SettingsPage;
use ActivePaymentsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use ActivePaymentsVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use ActivePaymentsVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
/**
 * Integrates WP Desk main settings page with WordPress
 *
 * @package WPDesk\Helper
 */
class SettingsIntegration implements \ActivePaymentsVendor\WPDesk\PluginBuilder\Plugin\Hookable, \ActivePaymentsVendor\WPDesk\PluginBuilder\Plugin\HookableCollection
{
    use HookableParent;
    /** @var SettingsPage */
    private $settings_page;
    public function __construct(\ActivePaymentsVendor\WPDesk\Helper\Page\SettingsPage $settingsPage)
    {
        $this->add_hookable($settingsPage);
    }
    /**
     * @return void
     */
    public function hooks()
    {
        $this->hooks_on_hookable_objects();
    }
}
