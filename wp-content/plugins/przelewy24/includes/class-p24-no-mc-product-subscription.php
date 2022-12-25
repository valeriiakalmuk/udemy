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
 * The product is not aware of multiple currencies.
 * We need this class before we have configured multi currency.
 */
class P24_No_Mc_Product_Subscription extends WC_Product {

	const TYPE = 'p24_subscription';

	/**
	 * Stores additional product data.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'days' => 1,
	);

	/**
	 * P24_Product_Simple constructor.
	 *
	 * @param int $product Id of product.
	 */
	public function __construct( $product = 0 ) {
		$this->product_type = self::TYPE;
		parent::__construct( $product );
	}

	/**
	 * Get days.
	 *
	 * @return int
	 */
	public function get_days() {
		$days = (int) $this->get_prop( 'days' );
		if ( $days < 1 ) {
			$days = 1;
		}

		return $days;
	}

	/**
	 * Set_days.
	 *
	 * @param int $days Number of days.
	 */
	public function set_days( $days ) {
		$days = (int) $days;
		if ( $days < 1 ) {
			$days = 1;
		}
		$this->set_prop( 'days', $days );
	}

}
