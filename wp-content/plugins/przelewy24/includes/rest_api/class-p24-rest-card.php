<?php
/**
 * File that define P24_Rest_Card class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * Class that support card API.
 */
class P24_Rest_Card extends P24_Rest_Abstract {

	/**
	 * Charge with 3ds.
	 *
	 * @param string $token Przelewy24 transaction token.
	 * @return array
	 */
	public function chargeWith3ds( $token ) {
		$path    = '/card/chargeWith3ds';
		$payload = array(
			'token' => $token,
		);
		$ret     = $this->call( $path, $payload, 'POST' );

		return $ret;
	}

    /**
     * Charge without 3ds.
     *
     * @param string $token Przelewy24 transaction token.
     * @return array
     */
    public function chargeWithout3ds( $token ) {
        $path    = '/card/charge';
        $payload = array(
            'token' => $token,
        );
        $ret     = $this->call( $path, $payload, 'POST' );

        return $ret;
    }

	/**
	 * Pay.
	 *
	 * @param array $payload Array with transaction data required to make card payment.
	 *
	 * @return array
	 */
	public function pay( $payload ) {
		$path = '/card/pay';
		$ret  = $this->call( $path, $payload, 'POST' );

		return $ret;
	}

	/**
	 * Info.
	 *
	 * @param string $order_id Order id used to collect card data with.
	 *
	 * @return array
	 */
	public function info( $order_id ) {
		return $this->call( '/card/info/' . $order_id, null, 'GET' );
	}
}
