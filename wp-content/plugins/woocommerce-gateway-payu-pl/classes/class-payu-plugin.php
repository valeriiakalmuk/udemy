<?php
/**
 * Class WC_Gateway_Payu_Plugin
 */
class WC_Gateway_Payu_Plugin extends \WGPayuVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin {



	const AJAX_ACTION_GET_SINGLE_CURRENCY = 'payu_get_single_currency';
	const NONCE_AJAX_ACTION_GET_SINGLE_CURRENCY = 'nonce_payu_get_single_currency';

	/**
	 * Script version
	 *
	 * @var string
	 */
	private $scripts_version = '12';

	/**
	 * Plugin path
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * Template path
	 *
	 * @var string
	 */
	public $template_path;

	/**
	 * Plugin namespace
	 *
	 * @var string
	 */
	public $plugin_namespace;

	/**
	 * 
	 * @var WC_Gateway_Payu
	 */
	private $payu_gateway;

	/**
	 * WC_Gateway_Payu_Plugin constructor.
	 *
	 * @param \WGPayuVendor\WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( \WGPayuVendor\WPDesk_Plugin_Info $plugin_info ) {
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
		$this->template_path      = $this->plugin_info->get_text_domain();

		$this->settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payu' );
		$this->docs_url     = 'https://www.wpdesk.pl/docs/payu-woocommerce-docs/';
	}

	/**
	 * Init plugin
	 */
	public function init() {
		$this->init_base_variables();
		$this->load_dependencies();
		$this->hooks();
	}

	/**
	 * Fires hooks
	 */
	public function hooks() {
		parent::hooks();
		add_filter( 'wcs_default_retry_rules', [ 'WC_Gateway_Payu_Recurring','get_retry_rules' ] );
		add_filter( 'woocommerce_payment_gateways', [ $this, 'woocommerce_payu_add' ] );
		add_action('wp_ajax_' . self::AJAX_ACTION_GET_SINGLE_CURRENCY, array($this, 'ajax_get_single_currency_process'));
	}

	public function ajax_get_single_currency_process(){
		try {
			if (!wp_verify_nonce(($_POST['security'] ?? ''), self::NONCE_AJAX_ACTION_GET_SINGLE_CURRENCY) && !current_user_can('manage_options')) {
				throw new RuntimeException('Error, you are not allowed to do this action');
			}

			$payu_gateway = $this->payu_gateway ?? new WC_Gateway_Payu();

			$currency = $_POST['currency'] ?? '';
			$currency = wp_kses_post( trim( stripslashes( $currency ) ) );

			if(!in_array( $currency, WPDesk_PayU_Settings::SUPPORTED_CURRENCIES )){
				throw new RuntimeException('Error, this currency is not supported');
			}



			wp_send_json([
				'success' => true,
				'content' => $payu_gateway->generate_single_pos($currency)
			]);
		} catch (\Exception $e) {
			wp_send_json([
				'success' => false,
				'message' => $e->getMessage()
			]);
		}
	}

	/**
	 * Load some dependencies
	 */
	public function load_dependencies() {
		require_once $this->plugin_path . '/inc/wpdesk-functions.php';
	}

	/**
	 * Add payment gateway.
	 *
	 * @param array $methods Methods.
	 *
	 * @return array
	 */
	public function woocommerce_payu_add( $methods ) {
		$this->payu_gateway = new WC_Gateway_Payu();
		if ( is_checkout()
		     && ( empty( $this->payu_gateway->settings['api_version'] ) || isset( $this->payu_gateway->settings['api_version'] ) && $this->payu_gateway->settings['api_version'] == 'classic_api' )
		     && get_woocommerce_currency() != 'PLN' ) {
			/* disable on Classic API and currency != PLN */
			return $methods;
		}

		$methods[] = $this->payu_gateway;
		$add       = true;

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( is_object( $screen ) && $screen->base == 'woocommerce_page_wc-settings' ) {
				$add = false;
			}
		}elseif ( is_admin() && isset($_GET['page']) && 'wc-settings' === $_GET['page'] && isset($_GET['tab']) && 'checkout' === $_GET['tab'] && !isset($_GET['section'])){
			$add = false;
		}

		if ( $add && $this->payu_gateway->get_option( 'payu_ia_enabled', 'no' ) == 'yes' ) {
			$payu_ia_method = new WC_Gateway_Payu_IA();
			$methods[]      = $payu_ia_method;
		}

		if ( $add && $this->payu_gateway->get_option( 'payu_subscriptions_enabled', 'no' ) == 'yes' ) {
			$payu_recurring_method = new WC_Gateway_Payu_Recurring();
			$methods[]             = $payu_recurring_method;
		}

		return $methods;
	}

	/**
	 * Admin enqueue scripts
	 */
	public function admin_enqueue_scripts() {
		$current_screen = get_current_screen();
		$suffix         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		if ( in_array( $current_screen->id, [ 'woocommerce_page_wc-settings' ] )
		     && isset( $_GET['tab'] ) && $_GET['tab'] == 'checkout'
		     && isset( $_GET['section'] ) && $_GET['section'] == 'payu'
		) {
			wp_register_style( 'payu_admin_css', $this->get_plugin_assets_url() . 'css/admin' . $suffix . '.css', [],  $this->scripts_version );
			wp_enqueue_style( 'payu_admin_css' );
			wp_enqueue_script( 'payu_admin_js', $this->get_plugin_assets_url() . 'js/admin' . $suffix . '.js', [ 'jquery' ], $this->scripts_version, true );
			$protocol = is_ssl() ? 'https://' : 'http://';
			wp_localize_script( 'payu_admin_js', 'payu_admin_object', [
				'site_url' => str_replace( $protocol, '', site_url() ),
				'protocol' => $protocol,
				'payu_nonce'     => wp_create_nonce( self::NONCE_AJAX_ACTION_GET_SINGLE_CURRENCY ),
			] );
			
			wp_register_style( 'jquery-ui-style', WC()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.min.css', array(), $this->scripts_version );
			wp_enqueue_script('jquery-ui-accordion');
			
		}
	}

	/**
	 * Plugin links
	 *
	 * @param array $links Links.
	 *
	 * @return array
	 */
	public function links_filter( $links ) {
		$plugin_links = array(
			'<a href="' . $this->settings_url . '">' . __( 'Ustawienia', 'woocommerce_payu' ) . '</a>',
			'<a href="' . $this->docs_url . '">' . __( 'Dokumentacja', 'woocommerce_payu' ) . '</a>',
			'<a href="https://www.wpdesk.pl/support/">' . __( 'Wsparcie', 'woocommerce_payu' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

}
