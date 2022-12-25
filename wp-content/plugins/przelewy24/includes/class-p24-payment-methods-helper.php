<?php
/**
 * File that define P24_Payment_Methods_helper class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * The helper for payment methods.
 */
class P24_Payment_Methods_Helper {

	/**
	 * Config.
	 *
	 * @var P24_Config_Accessor
	 */
	private $config;

	/**
	 * Data set.
	 *
	 * @var array
	 */
	private $data_set;

	/**
	 * P24_Icon_Svg_Generator constructor.
	 *
	 * @param P24_Config_Accessor $config A valid config.
	 */
	public function __construct( P24_Config_Accessor $config ) {
		$this->config = clone $config;
		$this->config->access_mode_to_strict();
	}

	/**
	 * Get set.
	 *
	 * @return array
	 */
	private function get_set() {
		if ( ! isset( $this->data_set ) ) {
			$rest_api = new P24_Rest_Common( $this->config );
			$response = $rest_api->payment_methods( 'pl' );
			if ( isset( $response['data'] ) ) {
				$this->data_set = $response['data'];
			} else {
				$this->data_set = array();
			}
		}

		return $this->data_set;
	}

	/**
	 * Has P24Now.
	 *
	 * @return bool
	 */
	public function has_p24_now() {
		$set = $this->get_set();
		foreach ( $set as $one ) {
			if ( 266 === $one['id'] ) {
				return true;
			}
		}
		return false;
	}
}
