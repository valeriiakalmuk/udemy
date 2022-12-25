<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WPDesk_Active_Payments_Tracker' ) ) {
	class WPDesk_Active_Payments_Tracker {

		public function __construct() {
			$this->hooks();
		}

		public function hooks() {
			add_filter( 'wpdesk_tracker_data', array( $this, 'wpdesk_tracker_data' ), 11 );
			add_filter( 'wpdesk_tracker_notice_screens', array( $this, 'wpdesk_tracker_notice_screens' ) );
			add_filter( 'wpdesk_track_plugin_deactivation', array( $this, 'wpdesk_track_plugin_deactivation' ) );
		}

		public function wpdesk_track_plugin_deactivation( $plugins ) {
			$plugins['woocommerce-active-payments/activepayments.php'] = 'woocommerce-active-payments/activepayments.php';

			return $plugins;
		}

		public function wpdesk_tracker_data( $data ) {

			$woocommerce_activepayments_options      = get_option( 'woocommerce_activepayments_options', array() );
			$woocommerce_activepayments_options_fees = get_option( 'woocommerce_activepayments_options_fees', array() );

			$plugin_data = array(
				'disable_payment_method' => floatval( 0 ),
				'fees_enabled'           => floatval( 0 ),
			);

			$payment          = new WC_Payment_Gateways();
			$payment_gateways = $payment->payment_gateways();

			foreach ( $payment_gateways as $key => $payment_gateway ) {
				if ( ! empty( $woocommerce_activepayments_options[ $key ] ) ) {
					$plugin_data['disable_payment_method'] ++;
				}
				if ( ! empty( $woocommerce_activepayments_options_fees[ $key ] )
				     && ! empty( $woocommerce_activepayments_options_fees[ $key ]['enabled'] )
				     && $woocommerce_activepayments_options_fees[ $key ]['enabled'] == '1'
				) {
					$plugin_data['fees_enabled'] ++;
				}
			}

			$data['active_payments'] = $plugin_data;

			return $data;
		}

		public function wpdesk_tracker_notice_screens( $screens ) {
			$current_screen = get_current_screen();
			if ( $current_screen->id == 'woocommerce_page_woocommerce_activepayments' ) {
				$screens[] = $current_screen->id;
			}

			return $screens;
		}

	}

}
