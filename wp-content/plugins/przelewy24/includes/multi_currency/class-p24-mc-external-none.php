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
class P24_MC_External_None implements P24_MC_Interface {

	/**
	 * Check if external multi currency is activated.
	 *
	 * This one is always inactive.
	 *
	 * @return bool
	 */
	public function is_multi_currency_active() {
		return false;
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
		return array();
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return '';
	}
}
