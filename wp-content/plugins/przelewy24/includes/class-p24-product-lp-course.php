<?php
/**
 * File that define P24_Product_LP_Course class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class fix support of multiple currencies in LearnPress.
 */
class P24_Product_LP_Course extends WC_Product_LP_Course {

	/**
	 * Convert_price.
	 *
	 * @param mixed $price The base price in LearnPress base currency.
	 * @return mixed
	 */
	private function convert_price( $price ) {
		$multi_currency = get_przelewy24_plugin_instance()->get_multi_currency_instance();
		$from           = learn_press_get_currency();
		$to             = $multi_currency->get_active_currency();
		return $multi_currency->convert_price( $price, $from, $to );
	}

	/**
	 * Get_price.
	 *
	 * @param string|null $context The name of context.
	 * @return mixed
	 */
	public function get_price( $context = 'view' ) {
		$native = parent::get_price( $context );
		$public = $this->convert_price( $native );

		return $public;
	}

}
