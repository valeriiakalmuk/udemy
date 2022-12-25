<?php
/**
 * File that define P24_Extra_Gateway class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * The class for extra gateway.
 */
class P24_Extra_Gateway extends WC_Payment_Gateway {

	/**
	 * Internal id.
	 *
	 * @var string
	 */
	private $internal_id;

	/**
	 * The id of BLIK method on Przelewy24 page.
	 */
	const BLIK_METHOD = '181';

	/**
	 * The name of form field on checkout page.
	 */
	const BLIK_CODE_INPUT_NAME = 'p24-blik-code';

	/**
	 * The key the blik code is stored in the database.
	 */
	const BLIK_CODE_META_KEY = '_p24_blik_code';

	/**
	 * Generator.
	 *
	 * @var Przelewy24Generator
	 */
	private $generator;

	/**
	 * Main_gateway.
	 *
	 * @var WC_Gateway_Przelewy24
	 */
	private $main_gateway;

	/**
	 * P24_Extra_Gateway constructor.
	 *
	 * @param string                $id Id of payment method.
	 * @param string                $title Title of payment method.
	 * @param Przelewy24Generator   $generator Przelewy24 generator.
	 * @param WC_Gateway_Przelewy24 $main_gateway Main Prelewy24 payment gateway.
	 * @param string                $icon Icon url.
	 */
	public function __construct( $id, $title, $generator, $main_gateway, $icon ) {
		$this->internal_id  = (string) $id;
		$this->id           = WC_Gateway_Przelewy24::PAYMENT_METHOD . '_extra_' . $id;
		$this->generator    = $generator;
		$this->main_gateway = $main_gateway;
		$this->icon         = $icon;
		$this->title        = (string) $title;

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'print_receipt' ) );
	}

	/**
	 * Get title.
	 *
	 * @return mixed
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Aditional conntent on print receipt page.
	 *
	 * @param int $order_id If of order.
	 */
	public function print_receipt( $order_id ) {
		$order_id = (int) $order_id;
		$is_blik  = self::BLIK_METHOD === $this->internal_id;
		$order    = new WC_Order( (int) $order_id );
		if ( $is_blik ) {
			$blik_code = $order->get_meta( self::BLIK_CODE_META_KEY );
		} else {
			$blik_code = false;
		}
		if ( $blik_code ) {
			$legacy_auto_submit = false;
		} else {
			$legacy_auto_submit = true;
		}
		$ajax_url = add_query_arg( array( 'wc-api' => 'wc_gateway_przelewy24' ), home_url( '/' ) );
		echo $this->generator->generate_przelewy24_form( $order, $legacy_auto_submit ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "<div id='p24-additional-order-data' data-order-id='$order_id' data-ajax-url='$ajax_url'></div>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $blik_code ) {
			echo P24_Blik_Html::get_modal_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id Id of orer.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );
		/* This is the default place to reduce stock levels. It is safe to call function below multiple times. */
		wc_maybe_reduce_stock_levels( $order );
		wp_verify_nonce( null ); /* There is no nonce in request. */
		if ( isset( $_POST[ self::BLIK_CODE_INPUT_NAME ] ) ) {
			$blik_code = sanitize_text_field( wp_unslash( $_POST[ self::BLIK_CODE_INPUT_NAME ] ) );
			$order->update_meta_data( P24_Core::CHOSEN_TIMESTAMP_META_KEY, time() );
			$order->update_meta_data( P24_Core::P24_METHOD_META_KEY, $this->internal_id );
			$order->update_meta_data( self::BLIK_CODE_META_KEY, $blik_code );
			$order->save_meta_data();
		}

		do_action( 'wc_extra_gateway_przelewy24_process_payment', $order );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( $order ),
		);
	}
}
