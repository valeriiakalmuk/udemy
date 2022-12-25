<?php
/**
 * File that define P24_Rest_Server class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * Base class for REST API transaction.
 */
class P24_Rest_Server {

	/**
	 * Config factory.
	 *
	 * @var callable
	 */
	protected $config_factory;

	/**
	 * Przelewy24RestAbstract constructor.
	 *
	 * @param callable $config_factory Config factory.
	 */
	public function __construct( $config_factory ) {
		$this->config_factory = $config_factory;
	}

	/**
	 * Support REST api.
	 */
	public function support_status() {
		$status_payload = $this->get_json_from_body();
		$cf             = $this->get_config( $status_payload['currency'] );
		$reg_session    = '/^[0-9a-zA-Z_\.]+$/D';
		if ( ! preg_match( $reg_session, $status_payload['sessionId'] ) ) {
			return;
		}
		$session_id = explode( '_', $status_payload['sessionId'] );
		$order_id   = $session_id[0];
		$order      = new WC_Order( $order_id );
		$verified   = $this->verify( $order, $status_payload, $cf );
		if ( $verified ) {
			/* This value mey be not set yet. */
			$order->update_meta_data( P24_Core::ORDER_P24_ID, $status_payload['orderId'] );
			$verified = $this->external_verify( $order, $status_payload, $cf );
		}
		if ( ! $verified ) {
			http_response_code( 400 );
			$answer = wp_json_encode( array( 'success' => false ) );
			exit( esc_js( "\n" . $answer ) );
		} else {
			$order->add_order_note( __( 'IPN payment completed', 'woocommerce' ) );
			$order->payment_complete();

			if ( (int) $status_payload['methodId'] ) {
				$user_id   = $order->get_user_id();
				$method_id = (int) $status_payload['methodId'];
				Przelewy24Helpers::setCustomData( 'user', $user_id, 'lastmethod', $method_id );
				Przelewy24Helpers::setCustomData( 'user', $user_id, 'accept', 1 );
				$this->save_reference_id( $status_payload['orderId'], $status_payload['methodId'], $user_id, $cf );
			}

			$order->update_meta_data( P24_Core::ORDER_SESSION_ID_KEY, $status_payload['sessionId'] );
			$order->save_meta_data();

			do_action( 'p24_payment_complete', $order, null );
		}

		$answer = wp_json_encode( array( 'success' => true ) );
		exit( esc_js( "\n" . $answer ) );
	}

	/**
	 * Save card id reference number (to allow one click payments).
	 *
	 * @param int                 $order_id P24 order id.
	 * @param int                 $method_id Method id (in p24).
	 * @param int                 $user_id Id of order owner.
	 * @param P24_Config_Accessor $cf Plugin configuration.
	 */
	public function save_reference_id( $order_id, $method_id, $user_id, $cf ) {
		if ( ! in_array( (int) $method_id, Przelewy24Class::getChannelsCard(), true ) ) {
			return;
		}
		if ( WC_Gateway_Przelewy24::get_cc_forget( $user_id ) ) {
			return;
		}
		if ( ! $cf->get_p24_payinshop() || ! $cf->get_p24_oneclick() ) {
			return;
		}

		$api_rest_card = new P24_Rest_Card( $cf );
		$res           = $api_rest_card->info( $order_id );

		if ( ! isset( $res['data']['refId'] ) ) {
			error_log( __METHOD__ . ' brak numeru referencyjnego karty użytkownika' );

			return;
		}
		$ref     = $res['data']['refId'];
		$expires = substr( $res['data']['cardDate'], 2 ) . substr( $res['data']['cardDate'], 0, 2 );

		if ( date( 'Ym' ) > $expires ) {
			error_log( __METHOD__ . ' termin ważności ' . var_export( $expires, true ) );

			return;
		}
		$key = md5( $res['data']['mask'] . '|' . $res['data']['cardType'] . '|' . substr( $expires, 2 ) );
		Przelewy24Helpers::setCustomData(
			'user_cards',
			$user_id,
			$key,
			array(
				'ref'  => $ref,
				'exp'  => substr( $expires, 2 ),
				'mask' => $res['data']['mask'],
				'type' => $res['data']['cardType'],
				'time' => date( 'Y-m-d H:i.s' ),
			)
		);
	}

	/**
	 * Verify payload.
	 *
	 * @param WC_Order            $order Order.
	 * @param array               $payload Payload.
	 * @param P24_Config_Accessor $config Config accessor.
	 * @return bool
	 */
	private function verify( $order, $payload, $config ) {
		$total_amount = number_format( $order->get_total() * 100, 0, '', '' );
		$saved_p24_order_id = $order->get_meta( P24_Core::ORDER_P24_ID );
		if ( $payload['merchantId'] !== (int) $config->get_merchant_id() ) {
			return false;
		} elseif ( $payload['posId'] !== (int) $config->get_shop_id() ) {
			return false;
		} elseif ( (string) $payload['amount'] !== $total_amount ) {
			return false;
		} elseif ( $payload['currency'] !== $config->get_currency() ) {
			return false;
		} elseif ( $saved_p24_order_id && (int) $saved_p24_order_id !== (int) $payload['orderId'] ) {
			return false;
		} elseif ( $this->sign( $payload, $config ) !== $payload['sign'] ) {
			return false;
		}

		return true;
	}

	/**
	 * External_verify.
	 *
	 * @param WC_Order            $order Order.
	 * @param array               $data Additional data.
	 * @param P24_Config_Accessor $config Config accessor.
	 *
	 * @return bool
	 */
	private function external_verify( $order, $data, $config ) {
		$rest_transaction = new P24_Rest_Transaction( $config );
		$payload          = array(
			'merchantId' => (int) $config->get_merchant_id(),
			'posId'      => (int) $config->get_shop_id(),
			'sessionId'  => $data['sessionId'],
			'amount'     => (int) number_format( $order->get_total() * 100, 0, '', '' ),
			'currency'   => $config->get_currency(),
			'orderId'    => (int) $order->get_meta( P24_Core::ORDER_P24_ID ),
		);

		return $rest_transaction->verify_bool( $payload );
	}

	/**
	 * Sign
	 *
	 * @param array               $payload Payload.
	 * @param P24_Config_Accessor $config Config accessor.
	 * @return string
	 */
	private function sign( $payload, $config ) {
		unset( $payload['sign'] );
		$payload['crc'] = $config->get_salt();
		$string         = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$sign           = hash( 'sha384', $string );

		return $sign;

	}

	/**
	 * Get JSON from body.
	 *
	 * @return array
	 */
	private function get_json_from_body() {
		$body = file_get_contents( 'php://input' );
		$json = json_decode( $body, true );
		if ( ! is_array( $json ) ) {
			$json = (array) $json;
		}

		return $json;
	}

	/**
	 * Get_config.
	 *
	 * @param string|null $currency Currency.
	 * @return P24_Config_Accessor Config accessor.
	 */
	private function get_config( $currency = null ) {
		$factory = $this->config_factory;
		$cf      = $factory( $currency );
		$cf->access_mode_to_strict();

		return $cf;
	}
}
