<?php
/**
 * File that define P24_External_Multi_Currency class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class with support for external multi currency.
 */
class P24_MC_External_WPML implements P24_MC_Interface {

	/**
	 * Get wpml settings.
	 *
	 * @return array
	 */
	private static function get_wpml_settings() {
		global $woocommerce_wpml;
		if ( $woocommerce_wpml ) {
			if ( isset( $woocommerce_wpml->settings['currencies_order'] ) ) {
				return $woocommerce_wpml->settings['currencies_order'];
			}
		}

		return array();
	}

	/**
	 * Try create instance.
	 *
	 * @return null|P24_MC_External_WPML
	 */
	public static function try_create() {
		$settings = self::get_wpml_settings();
		if ( $settings ) {
			return new self();
		} else {
			return null;
		}
	}

	/**
	 * Check if external multi currency is activated.
	 *
	 * @return bool
	 */
	public function is_multi_currency_active() {
		$settings = self::get_wpml_settings();

		return (bool) $settings;
	}

	/**
	 * Check if multi currency is internal.
	 *
	 * @return bool
	 */
	public function is_internal() {
		 return false;
	}

	/**
	 * Get list of available currencies.
	 *
	 * @return array
	 */
	public function get_available_currencies() {
		$settings = self::get_wpml_settings();

		return array_combine( $settings, $settings );
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'WPML';
	}
}
