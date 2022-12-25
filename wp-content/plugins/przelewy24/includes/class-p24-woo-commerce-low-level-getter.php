<?php
/**
 * File that define P24_Woo_Commerce_Low_Level_Getter class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that let access low level variables of Woo Commerce.
 *
 * It should be used with caution.
 */
class P24_Woo_Commerce_Low_Level_Getter
{
	/**
	 * Get currency from configuration of Woo Commerce.
	 *
	 * Try to bypass as many Woo Commerce hooks as possible.
	 *
	 * @return string
	 */
	public static function get_unhooked_currency_form_woocommerce() {
		return (string) get_option( P24_Woo_Commerce_Internals::CURRENCY );
	}
}
