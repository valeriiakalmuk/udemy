<?php
/**
 * File that define P24_Product_Simple class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 *
 *
 * The product is aware of multiple currencies.
 */
class P24_Product_Subscription extends P24_No_Mc_Product_Subscription {

	use P24_Product_Trait;

	/**
	 * P24_Product_Simple constructor.
	 *
	 * @param int $product Id of product.
	 */
	public function __construct( $product = 0 ) {
		$this->populate_internal_data();
		parent::__construct( $product );
	}

}
