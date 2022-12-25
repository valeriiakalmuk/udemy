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
interface P24_MC_Interface {

	/**
	 * Check if external multi currency is activated.
	 *
	 * @return bool
	 */
	public function is_multi_currency_active();

	/**
	 * Check if multi currency is internal.
	 *
	 * @return bool
	 */
	public function is_internal();

	/**
	 * Get list of available currencies.
	 *
	 * @return array
	 */
	public function get_available_currencies();

	/**
	 * Get name of multi currency implementation.
	 *
	 * @return string
	 */
	public function get_name();
}
