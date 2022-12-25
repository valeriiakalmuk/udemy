<?php
/**
 * File that define P24_Rest_Common class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * Class that support card API.
 */
class P24_Rest_Common extends P24_Rest_Abstract {

	/**
	 * Test access.
	 *
	 * @return array
	 */
	public function test_access() {
		$path = '/testAccess';
		$ret  = $this->call( $path, null, 'GET' );

		return $ret;
	}

	/**
	 * Test access bool.
	 *
	 * @return bool
	 */
	public function test_access_bool() {
		$data = $this->test_access();

		return isset( $data['error'] ) && empty( $data['error'] );
	}

	/**
	 * Payment_methods.
	 *
	 * @param string $lang One of supported languages (only 'pl' and 'en' for now).
	 *
	 * @return array
	 *
	 * @throws LogicException When wrong language is provided.
	 */
	public function payment_methods( $lang ) {
		if ( ! in_array( $lang, array( 'pl', 'en' ), true ) ) {
			throw new LogicException( 'The lang ' . $lang . ' is not supported.' );
		}
		$path = '/payment/methods/' . $lang;
		$ret  = $this->call( $path, null, 'GET' );

		return $ret;
	}
}
