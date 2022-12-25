<?php
/**
 * File that define P24_Rest_Transaction class.
 *
 * @package Przelewy24
 */
defined( 'ABSPATH' ) || exit;


/**
 * Class that support transaction API.
 */
class P24_Rest_Transaction extends P24_Rest_Abstract {

	/**
	 * Register.
	 *
	 * @param array $payload
	 * @return array
	 */
	public function register( $payload ) {
		$path            = '/transaction/register';
		$payload['sign'] = $this->sign_sha_384_register( $payload );

		return $this->call( $path, $payload, 'POST' );
	}

	/**
	 * Register raw token.
	 *
	 * @param array $payload Array with payload data.
	 *
	 * @return string|null
	 */
	public function register_raw_token( $payload ) {
		$res = $this->register( $payload );
		if ( isset( $res['data']['token'] ) ) {
			return $res['data']['token'];
		} else {
			return null;
		}
	}

	/**
	 * Register.
	 *
	 * @param array $payload
	 * @return array
	 */
	public function verify( $payload ) {
		$path            = '/transaction/verify';
		$payload['sign'] = $this->sign_sha_384_verify( $payload );

		return $this->call( $path, $payload, 'PUT' );
	}

	/**
	 * Register.
	 *
	 * @param array $payload Array with optional data -> status field.
	 *
	 * @return bool
	 */
	public function verify_bool( $payload ) {
		$res = $this->verify( $payload );
		if ( isset( $res['data']['status'] ) ) {
			$status = $res['data']['status'];
		} else {
			$status = '';
		}

		return 'success' === $status;
	}

	/**
	 * Register.
	 *
	 * @param array $payload Array with transaction refund data.
	 *
	 * @return array
	 */
	public function refund( $payload ) {
		$path = '/transaction/refund';
		return $this->call( $path, $payload, 'POST' );
	}

	/**
	 * By_session_id.
	 *
	 * @param string $session_id Session id of transaction to search for by REST api.
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException When invalid session id is provided.
	 */
	public function by_session_id( $session_id ) {
		/* RFC 3986 unreserved characters. */
		if ( ! preg_match( '/^[a-zA-Z0-9\\_\\.\\-\\~]+$/', $session_id ) ) {
			/* We do not support other than unreserved characters in $session_id. */
			throw new InvalidArgumentException( 'Invalid session id provided.' );
		}
		$path = '/transaction/by/sessionId/' . $session_id;

		return $this->call( $path, null, 'GET' );
	}

    /**
	 * Sign sha384.
	 *
	 * @param array $payload
	 * @return string
	 */
	private function sign_sha_384_register( $payload ) {
		$data   = array(
			'sessionId'  => $payload['sessionId'],
			'merchantId' => $payload['merchantId'],
			'amount'     => $payload['amount'],
			'currency'   => $payload['currency'],
			'crc'        => $this->cf->get_salt(),
		);
		$string = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$sign   = hash( 'sha384', $string );

		return $sign;
	}

	/**
	 * Sign sha384.
	 *
	 * @param array $payload
	 * @return string
	 */
	private function sign_sha_384_verify( $payload ) {
		$data   = array(
			'sessionId' => $payload['sessionId'],
			'orderId'   => $payload['orderId'],
			'amount'    => $payload['amount'],
			'currency'  => $payload['currency'],
			'crc'       => $this->cf->get_salt(),
		);
		$string = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$sign   = hash( 'sha384', $string );

		return $sign;
	}
}
