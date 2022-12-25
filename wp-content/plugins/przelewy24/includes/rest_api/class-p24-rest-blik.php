<?php
/**
 * File that define P24_Rest_Blik class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that support card API.
 */
class P24_Rest_Blik extends P24_Rest_Abstract {

	/**
	 * Charge by code
	 *
	 * @param string $token Przelewy24 transaction token.
	 * @param string $blik_code Blik code.
	 *
	 * @return array
	 */
	public function charge_by_code( $token, $blik_code ) {
		$path    = '/paymentMethod/blik/chargeByCode';
		$payload = array(
			'token'    => $token,
			'blikCode' => $blik_code,
		);
		$ret     = $this->call( $path, $payload, 'POST' );

		return $ret;
	}
}
