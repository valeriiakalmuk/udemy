<?php

namespace WGPayuVendor\WPDesk\WC;

class RequirementChecker
{
    /** @var string */
    private $plugin_name = '';
    /** @var string */
    private $plugin_file = '';
    /** @var string */
    private $min_wc_version;
    /** @var array */
    private $notices;
    public function __construct($plugin_file, $wc_version)
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_name = $this->extract_plugin_title_from_header($plugin_file);
        $this->min_wc_version = $wc_version;
        $this->notices = array();
    }
    /**
     * @param string $version
     *
     * @return $this
     */
    public function set_min_wc_require($version)
    {
        $this->min_wc_version = $version;
        return $this;
    }
    /**
     * @param string $file
     *
     * @return string mixed
     */
    private function extract_plugin_title_from_header($file)
    {
        $plugin_data = \get_file_data($file, array('title' => 'Plugin Name'));
        return $plugin_data['title'];
    }
    /**
     * @return bool
     */
    public function is_requirements_met()
    {
        $this->notices = $this->prepare_requirement_notices();
        return \count($this->notices) === 0;
    }
    /**
     * @return array
     */
    private function prepare_requirement_notices()
    {
        $notices = array();
        if (!\is_null($this->min_wc_version) && !$this->is_wc_at_least($this->min_wc_version)) {
            $notices[] = $this->prepare_notice_message(\sprintf(\__('The &#8220;%s&#8221; plugin cannot run on WooCommerce versions older than %s. Please update WooCommerce.', 'wpdesk-plugin'), \esc_html($this->plugin_name), $this->min_wc_version));
        }
        return $notices;
    }
    /**
     * @return void
     */
    public function disable_plugin_render_notice()
    {
        \add_action('admin_notices', array($this, 'deactivate_action'));
        \add_action('admin_notices', array($this, 'render_notices_action'));
    }
    /**
     * Shoud be called as WordPress action
     *
     * @return void
     */
    public function render_notices_action()
    {
        foreach ($this->notices as $notice) {
            echo $notice;
        }
    }
    /**
     * Prepares message in html format
     *
     * @param string $message
     *
     * @return string
     */
    private function prepare_notice_message($message)
    {
        return '<div class="error"><p>' . $message . '</p></div>';
    }
    /**
     * @return void
     */
    public function deactivate_action()
    {
        if (isset($this->plugin_file)) {
            \deactivate_plugins(\plugin_basename($this->plugin_file));
        }
    }
    /**
     * Checks if plugin is active and have designated version. Needs to be enabled in deferred way.
     *
     * @param string $min_version
     *
     * @return bool
     */
    public static function is_wc_at_least($min_version)
    {
        return \defined('WC_VERSION') && \version_compare(\WC_VERSION, $min_version, '>=');
    }
}
