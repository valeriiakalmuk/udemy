<?php
/**
 * File that define P24_Rest_Abstract class.
 *
 * @package Przelewy24
 */
defined( 'ABSPATH' ) || exit;


/**
 * Base class for REST API transaction.
 */
class P24_Rest_Abstract {


	const URL_SECURE  = 'https://secure.przelewy24.pl/api/v1';
	const URL_SANDBOX = 'https://sandbox.przelewy24.pl/api/v1';

	/**
	 * Url.
	 *
	 * @var string|null
	 */
	protected $url;

	/**
	 * Config accessor.
	 *
	 * @var P24_Config_Accessor
	 */
	protected $cf;

	/**
	 * Przelewy24RestAbstract constructor.
	 *
	 * @param P24_Config_Accessor $cf
	 */
	public function __construct( P24_Config_Accessor $cf ) {
		$this->cf = clone $cf;
		$this->cf->access_mode_to_strict();
		if ( $this->cf->is_p24_operation_mode( 'sandbox' ) ) {
			$this->url = self::URL_SANDBOX;
		} else {
			$this->url = self::URL_SECURE;
		}
	}

	/**
	 * Call rest command
	 *
	 * @param string            $path Api path.
	 * @param array|object|null $payload Method call parameters.
	 * @param string            $method Method name.
	 *
	 * @return array
	 */
	protected function call( $path, $payload, $method ) {
		$credentials = $this->cf->get_shop_id() . ':' . $this->cf->get_p24_api();
		$json_style  = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
		$options     = array(
			CURLOPT_USERPWD        => $credentials,
			CURLOPT_URL            => $this->url . $path,
			CURLOPT_RETURNTRANSFER => true,
		);
		if ( 'PUT' === $method ) {
			$options[ CURLOPT_CUSTOMREQUEST ] = 'PUT';
		}
		if ( 'GET' !== $method ) {
			$headers                       = array(
				'Content-Type: application/json',
			);
			$options[ CURLOPT_POST ]       = true;
			$options[ CURLOPT_HTTPHEADER ] = $headers;
			$options[ CURLOPT_POSTFIELDS ] = wp_json_encode( $payload, $json_style );
		}

		$h = curl_init();
		curl_setopt_array( $h, $options );
		$ret = curl_exec( $h );
		curl_close( $h );

		$decoded = json_decode( $ret, true );
		if ( ! is_array( $decoded ) ) {
			$decoded = array();
		}

		return $decoded;
	}
}
