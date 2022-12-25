<?php
/**
 * PayU Payment Gateway
 *
 * Provides a PayU Payment Gateway.
 *
 * @class    WC_Gateway_PayU
 * @package  WooCommerce
 * @category Payment Gateways
 * @author   Inspire Labs
 *
 */

if ( class_exists( 'WC_Payment_Gateway' ) ) {

	/**
	 * Class WC_Gateway_Payu_IA
	 */
	class WC_Gateway_Payu_IA extends WC_Gateway_Payu {

		const MIN_CART_TOTAL = 300;

		/**
		 * WC_Gateway_Payu_IA constructor.
		 */
		public function __construct() {
			parent::__construct();
			$this->id           = 'payu_ia';
			$this->method_title = __( 'PayU Raty', 'woocommerce_payu' );
			$this->has_fields   = false;
			$this->enabled = (WPDesk_PayU_Settings::DEFAULT_CURRENCY === get_woocommerce_currency() && $this->payu_settings->is_ia_gateway_enabled() ) ? 'yes' : 'no';


			$this->title       = $this->payu_settings->get_gateway_ia_checkout_title();
			$this->description = $this->payu_settings->get_gateway_ia_checkout_description();
			$this->icon        = $this->plugin_url() . '/assets/images/icon.png';


			add_action( 'woocommerce_receipt_payu_ia', [ $this, 'receipt_page' ] );

		}

		/**
		 * Is payment method available?
		 *
		 * @return bool
		 */
		public function is_available() {
			if ( parent::is_available() ) {
				return $this->check_cart_requirements();
			}

			return false;
		}

		private function check_cart_requirements() {
			$cart = WC()->cart;

			if ( ! empty( $cart ) ) {
				return floatval($cart->get_total('total')) >= self::MIN_CART_TOTAL;
			}

			return false;
		}

	}

}
