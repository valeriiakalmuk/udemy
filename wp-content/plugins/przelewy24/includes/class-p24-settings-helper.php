<?php
/**
 * File that define P24_Settings_Helper.
 *
 * @package Przelewy24
 */

/**
 * Helper to access config.
 *
 * There is some legacy code for this purpose in WC_Gateway_Przelewy24 too.
 */
class P24_Settings_Helper {

	const OPTION_KEY_COMMON            = 'przelewy24_common_settings';
	const OPTION_KEY_CURRENCY_TEMPLATE = 'woocommerce_przelewy24_%s_settings';

	/**
	 * Convert common arrays to config holder.
	 *
	 * @param array $array The config as array.
	 * @return P24_Config_Holder
	 */
	public static function map_array_to_config_holder( $array ) {
		$config_holder = new P24_Config_Holder();
		foreach ( $array as $k => $v ) {
			if ( property_exists( $config_holder, $k ) ) {
				$config_holder->{$k} = $v;
			} elseif ( 'CRC_key' === $k ) {
				$config_holder->salt = $v;
			} elseif ( 'p24_testmod' === $k ) {
				$config_holder->p24_operation_mode = $v;
			}
		}

		return $config_holder;
	}

	/**
	 * Get config for currency in object.
	 *
	 * @param string $currency The currency.
	 * @return P24_Config_Accessor|null
	 */
	public static function load_settings( $currency ) {
		$common_options = get_option( self::OPTION_KEY_COMMON, array() );
		if ( ! array_key_exists( 'enabled', $common_options ) ) {
			return null;
		} elseif ( 'yes' !== $common_options['enabled'] ) {
			return null;
		}

		$key           = sprintf( self::OPTION_KEY_CURRENCY_TEMPLATE, $currency );
		$array         = get_option( $key, array() );
		$config_holder = self::map_array_to_config_holder( $array );
		return new P24_Config_Accessor( $currency, $config_holder );
	}
}
