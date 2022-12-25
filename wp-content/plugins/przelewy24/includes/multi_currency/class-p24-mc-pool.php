<?php
/**
 * File that define P24_MC_Pool class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that pool all multi currency support.
 */
class P24_MC_Pool {

	/**
	 * Instance of core of plugin.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * External multi currency proxy.
	 *
	 * @var P24_MC_Interface|null
	 */
	private $external = null;

	/**
	 * Active currency.
	 *
	 * @var string|null
	 */
	private $active_currency = null;

	/**
	 * P24_MC_Pool constructor.
	 *
	 * @param P24_Core $plugin_core Instance of main plugin.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
	}

	/**
	 * Set_active_currency.
	 *
	 * @param string $currency Currency.
	 */
	public function set_active_currency( $currency ) {
		$this->active_currency = $currency;
	}

	/**
	 * Get external Multi Currency proxy.
	 *
	 * @return P24_MC_Interface
	 */
	public function get_external_mc() {
		if ( ! $this->external ) {
			$wpml = P24_MC_External_WPML::try_create();
			if ( $wpml && $wpml->is_multi_currency_active() ) {
				$this->external = $wpml;
			} else {
				$this->external = new P24_MC_External_None();
			}
		}

		return $this->external;
	}

	/**
	 * Get preffered Multi Currency proxy.
	 *
	 * @return P24_MC_Interface|null
	 */
	public function get_preffered_mc() {
		if ( $this->plugin_core->is_internal_multi_currency_active() ) {
			$mc = $this->plugin_core->get_multi_currency_instance();
		} else {
			$mc = $this->get_external_mc();
		}

		return $mc;
	}

	/**
	 * Method for filter that change default currency.
	 *
	 * The default value is ignored.
	 * The wrapped function should always return something.
	 *
	 * @param string|null $default Default value provided by filter.
	 * @return string|null
	 */
	public function check_admin_currency( $default ) {
		if ( ! $this->active_currency ) {
			$mc        = $this->get_preffered_mc();
			$available = $mc->get_available_currencies();
			wp_verify_nonce( null ); /* There is no noce. */
			if ( $this->plugin_core->is_in_json_mode() ) {
				if ( array_key_exists( P24_Request_Support::WP_JSON_GET_KEY_CURRENCY, $_GET ) ) {
					$this->active_currency = sanitize_text_field( wp_unslash( $_GET[ P24_Request_Support::WP_JSON_GET_KEY_CURRENCY ] ) );
				}
			} else {
				if ( isset( $_COOKIE['admin_p24_currency'] ) ) {
					$this->active_currency = sanitize_text_field( wp_unslash( $_COOKIE['admin_p24_currency'] ) );
				}
			}

			if ( ! $this->active_currency || ! array_key_exists( $this->active_currency, $available ) ) {
				$this->active_currency = P24_Woo_Commerce_Low_Level_Getter::get_unhooked_currency_form_woocommerce();
			}
		}

		return $this->active_currency ? $this->active_currency : $default;
	}

	/**
	 * Get list of available currencies.
	 *
	 * It is based on multipliers.
	 *
	 * @return array
	 */
	public function get_available_currencies() {
		return $this->get_preffered_mc()->get_available_currencies();
	}

	/**
	 * An AJAX method to change currency for admin.
	 */
	public function admin_ajax_change_currency() {
		$mc = $this->get_preffered_mc();
		if ( $mc->is_multi_currency_active() ) {
			header( 'Content-Type: text/plain; charset=utf-8' );
			wc_setcookie( 'admin_p24_currency', $this->check_admin_currency( '' ) );
			echo 'Ok';
			wp_die();
		}
	}

	/**
	 * Bind events to use multi currency.
	 */
	public function bind_events() {
		add_action( 'wp_ajax_p24_change_currency', array( $this, 'admin_ajax_change_currency' ) );
		add_filter( 'przelewy24_multi_currency_admin_currency', array( $this, 'check_admin_currency' ) );
		add_filter( 'przelewy24_multi_currency_options', array( $this, 'get_available_currencies' ) );
	}

}
