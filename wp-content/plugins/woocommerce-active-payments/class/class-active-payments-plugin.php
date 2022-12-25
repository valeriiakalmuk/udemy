<?php

use \ActivePaymentsVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;

/**
 * Class WooCommerce_Active_Payments_Plugin
 *
 * Main plugin class.
 */
class WPDesk_Active_Payments_Plugin extends AbstractPlugin {

	use \ActivePaymentsVendor\WPDesk\PluginBuilder\Plugin\TemplateLoad;

	/**
	 * Scripts version
	 *
	 * @var int
	 */
	const SCRIPTS_VERSION = 2;

	/**
	 * Plugin URL
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Option slug for backward compatibilty
	 *
	 * @var string
	 */
	const OPTION_SLUG = 'woocommerce_activepayments';

	/**
	 * Scripts version combined by plugin version & script version
	 *
	 * @var string
	 */
	private $scripts_version;

	/**
	 * Print_Orders_Address_Label_Plugin constructor.
	 *
	 * @param ActivePaymentsVendor\WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( ActivePaymentsVendor\WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
		parent::__construct( $this->plugin_info );
	}

	/**
	 * Init base variables for plugin
	 */
	public function init_base_variables() {
		$this->plugin_url         = $this->plugin_info->get_plugin_url();
		$this->plugin_path        = $this->plugin_info->get_plugin_dir();
		$this->plugin_text_domain = $this->plugin_info->get_text_domain();
		$this->plugin_namespace   = $this->plugin_info->get_text_domain();
		$this->scripts_version    = $this->plugin_info->get_version() . '.' . self::SCRIPTS_VERSION;
	}

	/**
	 * Get plugin path
	 *
	 * @return string
	 */
	public function get_plugin_path() {
		return $this->plugin_info->get_plugin_dir();
	}

	/**
	 * Init plugin
	 */
	public function init() {
		parent::init();
		$this->init_base_variables();
		new WPDesk_Active_Payments_Tracker();
		( new ActivePayments( $this ) )->hooks();
		if ( is_admin() ) {
			( new ActivePaymentsAdmin( $this ) )->hooks();
		}
	}

	/**
	 * Load plugin textdomain
	 *
	 * Backward compatibility (bad texdomain & namespaces)
	 *
	 * @return void
	 */
	public function load_plugin_text_domain() {
		load_plugin_textdomain( $this->get_text_domain(), false, 'woocommerce-active-payments/lang/' );
	}

	/**
	 * Enqueue admin scripts
	 */
	public function admin_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_style(
			'active_payments_admin_css',
			trailingslashit( $this->get_plugin_assets_url() ) . 'css/admin' . $suffix . '.css',
			array(),
			$this->scripts_version
		);
		wp_enqueue_style( 'active_payments_admin_css' );

		wp_register_script(
			'active_payments_admin_js',
			trailingslashit( $this->get_plugin_assets_url() ) . 'js/admin' . $suffix . '.js',
			array( 'jquery' ),
			$this->scripts_version,
			true
		);
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'active_payments_admin_js' );
	}

	/**
	 * Enqueue front scripts
	 */
	public function wp_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		if ( is_checkout() ) {
			wp_enqueue_script( 'woocommerce-activepayments-front', $this->get_plugin_assets_url() . '/js/front' . $suffix . '.js', array( 'jquery' ), $this->scripts_version, true );
		}
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function get_settings() {
		return get_option( 'woocommerce_activepayments_options', array() );
	}

	/**
	 * Get setting value
	 *
	 * @param string $name    Setting name.
	 * @param mixed  $default Default value.
	 *
	 * @return mixed|void
	 */
	public function get_setting_value( $name, $default = '' ) {
		return get_option( self::OPTION_SLUG . '_' . $name, $default );
	}

	/**
	 * Update setting value
	 *
	 * @param string $name  Setting name.
	 * @param mixed  $value Value.
	 *
	 * @return mixed|void
	 */
	public function update_setting_value( $name, $value ) {
		update_option( self::OPTION_SLUG . '_' . $name, $value );
	}

	/**
	 * Action links
	 *
	 * @param array $links Links.
	 *
	 * @return array
	 */
	public function links_filter( $links ) {

		$docs_link    = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/docs/aktywne-platnosci-woocommerce-docs/' : 'https://www.wpdesk.net/docs/active-payments-woocommerce-docs/';
		$support_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/support/' : 'https://www.wpdesk.net/support';

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=woocommerce_activepayments' ) . '">' . esc_html__( 'Settings', 'woocommerce_activepayments' ) . '</a>',
			'<a href="' . $docs_link . '">' . esc_html__( 'Docs', 'woocommerce_activepayments' ) . '</a>',
			'<a href="' . $support_link . '">' . esc_html__( 'Support', 'woocommerce_activepayments' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}
}
