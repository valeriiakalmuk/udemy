<?php
/**
 * File that define P24_Woo_Commerce_Internals class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * Class that defines few constants used by Woo Commerce.
 */
class P24_Woo_Commerce_Internals {

	const CURRENCY           = 'woocommerce_currency';
	const CURRENCY_POS       = 'woocommerce_currency_pos';
	const HOLD_STOCK_MINUTES = 'woocommerce_hold_stock_minutes';
	const MANAGE_STOCK       = 'woocommerce_manage_stock';
	const PRICE_THOUSAND_SEP = 'woocommerce_price_thousand_sep';
	const PRICE_DECIMAL_SEP  = 'woocommerce_price_decimal_sep';
	const PRICE_NUM_DECIMALS = 'woocommerce_price_num_decimals';
	const PROCESSING_STATUS  = 'wc-processing';
	const TRANSLATION_DOMAIN = 'woocommerce';
}
