<?php
/**
 * File that define P24_Config_Eraser class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class to erase Przelewy24 configuration.
 */
class P24_Config_Eraser {

	const SLUG_CLEAR_CONFIG = 'p24_clear_config';

	/**
	 * Instance of core of plugin.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * Construct class instance.
	 *
	 * @param P24_Core $plugin_core The core class for plugin.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
	}

	/**
	 * Get kay of option record from database.
	 *
	 * It is different for each currency.
	 *
	 * @param string $for_currency Code of currency.
	 *
	 * @return string
	 */
	public static function get_key_for_config_for_currency( $for_currency ) {
		$code = strtolower( $for_currency );

		return 'woocommerce_' . WC_Gateway_Przelewy24::PAYMENT_METHOD . '_' . $code . '_settings';
	}

	/**
	 * Execute clear payment config.
	 */
	private function execute_clear_payment_config() {
		$currencies = $this->plugin_core->get_available_currencies_or_default();
		foreach ( $currencies as $currency ) {
			$key = self::get_key_for_config_for_currency( $currency );
			delete_option( $key );
		}
		delete_option( P24_Settings_Helper::OPTION_KEY_COMMON );
		delete_option( 'p24_statuses_active' );
		delete_option( P24_Status_Provider::ADDITIONAL_STATUSES_KEY );
		delete_option( 'przelewy24_multi_currency_formats' );
		delete_option( 'przelewy24_multi_currency_multipliers' );
		delete_option( P24_Install::P24_INSTALLED_VERSION );
		delete_option( 'przelewy24_subscriptions_days' );
		delete_option( 'przelewy24_subscriptions_active' );
		delete_option( 'p24_subscription_page_id' );
	}

	/**
	 * Render page to clear config.
	 */
	public function render_clear_config() {
		$params = array(
			'field_name' => strtr( self::SLUG_CLEAR_CONFIG, '-', '_' ),
			'deleted'    => false,
		);

		if ( isset( $_POST['p24_nonce'] ) ) {
			$nonce = sanitize_key( $_POST['p24_nonce'] );
			if ( wp_verify_nonce( $nonce, 'p24_action' ) ) {
				if ( isset( $_POST[ $params['field_name'] ] ) ) {
					$this->execute_clear_payment_config();
					$params['deleted'] = true;
				}
			}
		}

		$this->plugin_core->render_template( 'confirm-clear-config', $params );
	}

	/**
	 * Prepare additional config pages.
	 */
	public function prepare_pages() {
		add_submenu_page(
			'wc-settings',
			__( 'Wyczyść ustawienia' ),
			__( 'Wyczyść ustawienia' ),
			'delete_plugins',
			self::SLUG_CLEAR_CONFIG,
			array( $this, 'render_clear_config' )
		);
	}

	/**
	 * Add link for plugins page
	 *
	 * @param array  $actions Default actions.
	 * @param string $plugin_file Main filename of plugin.
	 * @param array  $plugin_data Plugin data.
	 * @param string $context Context.
	 * @return array
	 */
	public function add_plugin_action_link( $actions, $plugin_file, $plugin_data, $context ) {
		if ( 'przelewy24/woocommerce-gateway-przelewy24.php' !== $plugin_file ) {
			return $actions;
		} elseif ( current_user_can( 'delete_plugins' ) ) {
			$url = menu_page_url( self::SLUG_CLEAR_CONFIG, false );

			$actions['clear'] = '<a href="' . $url . '">' . __( 'Wyczyść ustawienia' ) . '</a>';

			return $actions;
		} else {
			return $actions;
		}
	}

	/**
	 * Bind events.
	 */
	public function bind_events() {
		add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_link' ), 10, 4 );
		add_action( 'admin_menu', array( $this, 'prepare_pages' ) );
	}
}
