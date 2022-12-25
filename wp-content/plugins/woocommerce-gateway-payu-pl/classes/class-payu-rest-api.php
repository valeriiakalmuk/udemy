<?php
/**
 * Created by PhpStorm.
 * User: gro
 * Date: 27.08.2017
 * Time: 13:41
 */

class WPDesk_PayU_Rest_API {

	/**
	 * 
	 * @var WPDesk_PayU_Settings
	 */
	private $settings;

	private $api_url;

	private $bearer = false;

	const PARAM_NAME_HASH = 'hash';
	const PARAM_NAME_ERROR = 'error';

	public function __construct( WPDesk_PayU_Settings $settings ) {
		$this->settings = $settings;
		$this->api_url = $settings->is_sandbox()? 'https://secure.snd.payu.com' : 'https://secure.payu.com';
	}

	private function get_bearer( string $client_id, string $client_secret ) {
		if ( $this->bearer === false ) {
			$transient_name = 'payu_bearer_' . md5( $client_id . $client_secret );
			$this->bearer   = get_transient( $transient_name );
			if ( $this->bearer === false ) {
				$args     = [
					'sslverify' => false,
					'body'      => [
						'grant_type'    => 'client_credentials',
						'client_id'     => $client_id,
						'client_secret' => $client_secret,
					],
				];
				$url      = trailingslashit( $this->api_url ) . 'pl/standard/user/oauth/authorize';
				$response = wp_remote_post( $url, $args );
				if ( is_wp_error( $response ) ) {
					throw new Exception( $response->get_error_message() );
				}
				if ( $response['response']['code'] != '200' ) {
					throw new Exception( $response['response']['message'] );
				}
				$json         = json_decode( $response['body'] );
				$this->bearer = $json->access_token;
				set_transient( $transient_name, $this->bearer, intval( $json->expires_in ) - 60 );
			}
		}

		return $this->bearer;
	}

	/**
	 * @param $order WC_Order
	 *
	 * @return string
	 */
	public function get_notify_url( $order ) {
		if( $order->get_total() == 0 ){
			return home_url( '?wc-api=WC_Gateway_Payu&rest_api=1&order_id=' . wpdesk_get_order_id( $order ).'&order_type=trial' );
		}else{
			return home_url( '?wc-api=WC_Gateway_Payu&rest_api=1&order_id=' . wpdesk_get_order_id( $order ) );
		}
	}

	/**
	 * Continue url for PayU call
	 *
	 * @param $order WC_Order
	 */
	public function get_continue_url( $order ) {
		$url = $this->get_notify_url( $order );

		return add_query_arg( [ self::PARAM_NAME_HASH => $this->prepare_hash( $order ) ], $url );
	}

	private function prepare_hash( WC_Order $order ) {
		return md5( NONCE_SALT . $order->get_order_key() );
	}

	/**
	 * Is $_GET hash valid
	 *
	 * @param WC_Order $order
	 * @param string $hash
	 *
	 * @return bool
	 */
	public function is_hash_valid( WC_Order $order, $hash ) {
		return $this->prepare_hash( $order ) === $hash;
	}

	/**
	 * Get order amount as integer.
	 *
	 * @param float $amount Order.
	 *
	 * @return int
	 */
	private function get_order_amount_as_integer( $amount ) {
		return floor( round( $amount * 100 ) );
	}

	/**
	 * @param $order WC_Order
	 *
	 * @throws Exception
	 */
	public function create_order(
		$order,
		$order_id,
		$ia = false,
		$subs = false,
		$payu_card_data = [],
		$recurring = false,
		$amount = 0
	) {
		$pos = $this->settings->get_pos_from_currency( $order->get_currency() );
		$siteurl = parse_url( get_bloginfo( 'url' ) );
		$bearer  = $this->get_bearer( $pos->get_client_id(), $pos->get_client_secret() );
		$description = sprintf(
		// Translators: shop title and order nummber.
			__( '%1$s, Zamówienie %2$s', 'woocommerce_payu' ),
			$siteurl['host'],
			$order->get_order_number()
		);
		
		// Get server's IP address if the customer IP is empty
		if (!empty(wpdesk_get_order_meta( $order, '_customer_ip_address', true ))) {
		$payu_customer_ip = wpdesk_get_order_meta( $order, '_customer_ip_address', true );
		} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
		$payu_customer_ip = $_SERVER['REMOTE_ADDR'];
		}

		$int_amount = $this->get_order_amount_as_integer( floatval($amount) );

		$data    = [
			'notifyUrl'     => $this->get_notify_url( $order ),
			'continueUrl'   => $this->get_continue_url( $order ),
			'customerIp'    => $payu_customer_ip,
			'merchantPosId' => $pos->get_pos_id(),
			'description'   => apply_filters( 'wpdesk_payu_create_order_description', $description, $order, $siteurl ),
			'currencyCode'  => wpdesk_get_order_meta( wpdesk_get_order_id( $order ), '_currency', true ),
			'totalAmount'   => $int_amount,
			'extOrderId'    => $order_id . '_' . md5( uniqid( $order_id, true ) ),
			'buyer'         => [
				'email'     => wpdesk_get_order_meta( $order, '_billing_email', true ),
				'phone'     => wpdesk_get_order_meta( $order, '_billing_phone', true ),
				'firstName' => wpdesk_get_order_meta( $order, '_billing_first_name', true ),
				'lastName'  => wpdesk_get_order_meta( $order, '_billing_last_name', true ),
			],
			'settings'      => [
				'invoiceDisabled' => 'true',
			],
			'products'      => [
				[
					'name'      => sprintf( __( 'Zamówienie %s', 'woocommerce_payu' ), $order->get_order_number() ),
					'unitPrice' => $int_amount,
					'quantity'  => 1
				]
			]
		];

		if ( $recurring ) {
			$data['recurring'] = $recurring;
		}

		if ( $ia ) {
			$data['payMethods'] = [
				'payMethod' => [
					'type'  => 'PBL',
					'value' => 'ai'
				],
			];
		}

		if ( $subs ) {
			$data['payMethods'] = [
				'payMethod' => [
					'type'  => 'CARD_TOKEN',
					'value' => $payu_card_data['value'],
				],
			];
		}
		$args = [
			'redirection' => 0,
			'sslverify'   => false,
			'headers'     => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $bearer
			],
			'body'        => json_encode( $data ),
			'timeout'     => 45
		];


		$url      = trailingslashit( $this->api_url ) . 'api/v2_1/orders';
		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}
		if ( $response['response']['code'] != '302' && $response['response']['code'] != '201' ) {
			$json = json_decode( $response['body'], true );
			if ( isset( $json['status'] ) ) {
				$message = '';
				if ( isset( $json['status']['code'] ) ) {
					$message .= $json['status']['code'];
				}
				if ( isset( $json['status']['codeLiteral'] ) ) {
					if ( $message != '' ) {
						$message .= ' - ';
					}
					$message .= $json['status']['codeLiteral'];
				}
				if ( isset( $json['status']['statusDesc'] ) ) {
					if ( $message != '' ) {
						$message .= ' - ';
					}
					$message .= $json['status']['statusDesc'];
				}
				throw new Exception( $message );
			}
			throw new Exception( $response['response']['message'] );
		}
		$json = json_decode( $response['body'], true );

		return $json;
	}

	public function get_order( string $currency, string $order_id ) {
		$pos = $this->settings->get_pos_from_currency( $currency );
		$bearer   = $this->get_bearer( $pos->get_client_id(), $pos->get_client_secret() );
		$args     = [
			'redirection' => 0,
			'sslverify'   => false,
			'headers'     => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $bearer
			],
		];
		$url      = trailingslashit( $this->api_url ) . 'api/v2_1/orders/' . $order_id;
		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}
		if ( $response['response']['code'] != '200' ) {
			$json = json_decode( $response['body'], true );
			if ( isset( $json['status'] ) ) {
				$message = '';
				if ( isset( $json['status']['code'] ) ) {
					$message .= $json['status']['code'];
				}
				if ( isset( $json['status']['codeLiteral'] ) ) {
					if ( $message != '' ) {
						$message .= ' - ';
					}
					$message .= $json['status']['codeLiteral'];
				}
				if ( isset( $json['status']['statusDesc'] ) ) {
					if ( $message != '' ) {
						$message .= ' - ';
					}
					$message .= $json['status']['statusDesc'];
				}
				throw new Exception( $message );
			}
			throw new Exception( $response['response']['message'] );
		}
		$json = json_decode( $response['body'], true );

		return $json;
	}

	public function get_multi_currency_data( string $currency ) {
		$pos = $this->settings->get_pos_from_currency( $currency );
		$bearer   = $this->get_bearer( $pos->get_client_id(), $pos->get_client_secret() );
		$args     = [
			'redirection' => 0,
			'sslverify'   => false,
			'headers'     => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $bearer
			],
		];

		$endpoint = 'api/v2_1/fx-providers/ecb/fx-rates?termCurrency=PLN';
		$url      = trailingslashit( $this->api_url ) . $endpoint;
		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}
		if ( $response['response']['code'] != '200' ) {
			$json = json_decode( $response['body'], true );
			if ( isset( $json['status'] ) ) {
				$message = '';
				if ( isset( $json['status']['code'] ) ) {
					$message .= $json['status']['code'];
				}
				if ( isset( $json['status']['codeLiteral'] ) ) {
					if ( $message != '' ) {
						$message .= ' - ';
					}
					$message .= $json['status']['codeLiteral'];
				}
				if ( isset( $json['status']['statusDesc'] ) ) {
					if ( $message != '' ) {
						$message .= ' - ';
					}
					$message .= $json['status']['statusDesc'];
				}
				throw new Exception( $message );
			}
			throw new Exception( $response['response']['message'] );
		}
		$json = json_decode( $response['body'], true );

		return $json;
	}

	/**
	 * Refund.
	 *
	 * @param string            $order_id Order id.
	 * @param float|string|null $amount Amount.
	 * @param string            $reason Reason.
	 *
	 * @return array|mixed|object
	 * @throws Exception Exception.
	 */
	public function refund( string $currency, string $order_id, $amount = null, $reason = '' ) {
		$pos = $this->settings->get_pos_from_currency( $currency );
		$bearer = $this->get_bearer( $pos->get_client_id(), $pos->get_client_secret() );
		$data   = [
			'refund' => [
				'description' => $reason,
			],
		];
		if ( null !== $amount ) {
			$data['refund']['amount'] = round( floatval( $amount ) * 100 );
		}
		$args     = [
			'redirection' => 0,
			'sslverify'   => false,
			'headers'     => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $bearer,
			],
			'body'        => json_encode( $data ),
		];
		$url      = trailingslashit( $this->api_url ) . 'api/v2_1/orders/' . $order_id . '/refunds';
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}
		if ( intval( $response['response']['code'] ) !== 200 ) {
			$this->throw_exception( $response );
		}
		$json = json_decode( $response['body'], true );

		return $json;
	}

	/**
	 * Throw exception.
	 *
	 * @param array $response Response.
	 *
	 * @throws WPDesk_PayU_Rest_API_Exception Exception.
	 */
	private function throw_exception( $response ) {
		$json = json_decode( $response['body'], true );
		if ( isset( $json['status'] ) ) {
			$message = '';
			if ( isset( $json['status']['code'] ) ) {
				$message .= $json['status']['code'];
			}
			if ( isset( $json['status']['statusCode'] ) ) {
				$message .= $json['status']['statusCode'];
			}
			if ( isset( $json['status']['codeLiteral'] ) ) {
				if ( '' !== $message ) {
					$message .= ' - ';
				}
				$message .= $json['status']['codeLiteral'];
			}
			if ( isset( $json['status']['statusDesc'] ) ) {
				if ( '' !== $message ) {
					$message .= ' - ';
				}
				$message .= $json['status']['statusDesc'];
			}
			throw new WPDesk_PayU_Rest_API_Exception( $message );
		}
		throw new WPDesk_PayU_Rest_API_Exception( $response['response']['message'] );
	}

}
