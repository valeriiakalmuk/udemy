<?php
/**
 * File that define P24_Sub_Generator.
 *
 * @package Przelewy24
 */

/**
 * Class tat generate data to generate orders.
 *
 * It has some interaction with WC_Gateway_Przelewy24 .
 */
class P24_Sub_Generator {

	/**
	 * Find_saved_card.
	 *
	 * @param string $key Kay of card, it is not reference id for Przelewy24.
	 * @param int    $user_id User id.
	 * @return array|null
	 */
	public static function find_saved_card( $key, $user_id ) {
		$cards = WC_Gateway_Przelewy24::get_all_cards( $user_id );
		foreach ( $cards as $card ) {
			if ( $card->custom_key === $key ) {
				return $card->custom_value;
			}
		}

		return null;
	}

	/**
	 * Generate order description.
	 *
	 * @param WC_Order $order The existing order.
	 */
	public static function generate_order_description( $order ) {
		$currency = $order->get_currency();
		$config   = P24_Settings_Helper::load_settings( $currency );
		$config->access_mode_to_strict();

		$description_order_id = $order->get_order_number();
		/* Modifies order number if Sequential Order Numbers Pro plugin is installed. */
		if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			$seq                  = new WC_Seq_Order_Number_Pro();
			$description_order_id = $seq->get_order_number( $description_order_id, $order );
		} elseif ( class_exists( 'WC_Seq_Order_Number' ) ) {
			$seq                  = new WC_Seq_Order_Number();
			$description_order_id = $seq->get_order_number( $description_order_id, $order );
		}

		$now        = new DateTime();
		$short_date = $now->format( 'Ymdhi' );
		/* Description depends on operation mode. */
		$prefix = $config->is_p24_operation_mode( 'sandbox' ) ? __( 'Transakcja testowa', 'przelewy24' ) . ', ' : '';
		$suffix = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . ', ' . $short_date;
		$desc   = $prefix . __( 'Zamówienie nr', 'przelewy24' ) . ': ' . $description_order_id . ', ' . $suffix;

		return $desc;
	}

	/**
	 * Generate payload for REST transaction.
	 *
	 * @param WC_Order $order The existing order.
	 * @return array|null
	 */
	public static function generate_payload_for_rest( $order ) {
		$currency = $order->get_currency();
		$config   = P24_Settings_Helper::load_settings( $currency );
		if ( ! $config ) {
			return null;
		}

		/* We need a plain integer to generate session id. */
		$order_id = (int) $order->get_id();
		if ( ! $order_id ) {
			return null;
		}

		$amount     = (int) round( $order->get_total() * 100 );
		$shipping   = (int) round( $order->get_shipping_total() * 100 );
		$session_id = addslashes( $order_id . '_' . uniqid( md5( wp_rand() ), true ) );

		$status_page = add_query_arg(
			array(
				'wc-api' => 'WC_Gateway_Przelewy24',
				'status' => 'REST',
			),
			home_url( '/' )
		);
		$return_page = WC_Gateway_Przelewy24::getReturnUrlStatic( $order );

		global $locale;
		$localization = ! empty( $locale ) ? explode( '_', $locale ) : 'pl';

		return array(
			'merchantId'  => (int) $config->get_merchant_id(),
			'posId'       => (int) $config->get_shop_id(),
			'sessionId'   => $session_id,
			'amount'      => $amount,
			'currency'    => $currency,
			'description' => self::generate_order_description( $order ),
			'email'       => filter_var( $order->get_billing_email(), FILTER_SANITIZE_EMAIL ),
			'client'      => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'address'     => $order->get_billing_address_1(),
			'zip'         => $order->get_billing_postcode(),
			'city'        => $order->get_billing_city(),
			'country'     => $order->get_billing_country(),
			'language'    => filter_var( $localization[0], FILTER_SANITIZE_STRING ),
			'urlReturn'   => filter_var( $return_page, FILTER_SANITIZE_URL ),
			'urlStatus'   => filter_var( $status_page, FILTER_SANITIZE_URL ),
			'shipping'    => $shipping,
			'encoding'    => 'UTF-8',
		);
	}
}
