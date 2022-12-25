<?php

/**
 * Class ActivePayments
 *
 * Filter active gateways and cart calculate.
 */
class ActivePayments {

	const SHIPPING_RATE_DELIMITER = ':';

	const SHIPPING_RATE_FULL_NAME_ALL_PARTS_COUNT = 3;
	const SHIPPING_METHOD_NAME_PART = 0;
	const SHIPPING_ZONE_ID_PART = 1;

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Plugin.
	 *
	 * @var WPDesk_Active_Payments_Plugin Plugin.
	 */
	private $plugin;

	/**
	 * Has settings
	 *
	 * @var bool
	 */
	protected $is_without_any_settings;


	/**
	 * ActivePayments constructor.
	 *
	 * @param WPDesk_Active_Payments_Plugin $plugin Plugin.
	 */
	public function __construct( WPDesk_Active_Payments_Plugin $plugin ) {
		$this->plugin                  = $plugin;
		$this->settings                = $plugin->get_settings();
		$this->is_without_any_settings = empty( $this->settings );
	}

	/**
	 * Fires hooks
	 */
	public function hooks() {
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_active_gateways' ), 100 );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'woocommerce_cart_calculate_fees' ), 100 );
		add_filter( 'wpdesk_value_in_currency', array( $this, 'wpdesk_value_in_currency_wpml' ), 1 );
		add_filter( 'wpdesk_value_in_currency', array( $this, 'wpdesk_value_in_currency_aelia' ), 1 );
		add_filter( 'wpdesk_value_in_currency', array( $this, 'wpdesk_value_in_currency_wmcs' ), 1 );
		add_filter( 'wpdesk_value_in_currency', array( $this, 'wpdesk_value_in_currency_woocs' ), 1 );
	}


	/**
	 * Added support for WPML
	 *
	 * @param string $value Value.
	 *
	 * @return mixed|void
	 */
	public function wpdesk_value_in_currency_wpml( $value ) {
		return $value;
	}

	/**
	 * Added support for Aelia Currency Switcher
	 *
	 * @param string $value Value.
	 *
	 * @return mixed|void
	 */
	public function wpdesk_value_in_currency_aelia( $value ) {
		if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) {
			$aelia          = WC_Aelia_CurrencySwitcher::instance();
			$aelia_settings = WC_Aelia_CurrencySwitcher::settings();
			$from_currency  = $aelia_settings->base_currency();
			$to_currency    = $aelia->get_selected_currency();
			$value          = $aelia->convert( $value, $from_currency, $to_currency );
		}
		return $value;
	}

	/**
	 * Added support for WMCS
	 *
	 * @param string $value Value.
	 *
	 * @return mixed
	 */
	public function wpdesk_value_in_currency_wmcs( $value ) {
		if ( function_exists( 'wmcs_convert_price' ) ) {
			$value = wmcs_convert_price( $value );
		}
		return $value;
	}

	/**
	 * Added support for WOOCS
	 *
	 * @param string $value Value.
	 *
	 * @return mixed
	 */
	public function wpdesk_value_in_currency_woocs( $value ) {
		if ( isset( $GLOBALS['WOOCS'] ) ) {
			$value = $GLOBALS['WOOCS']->woocs_exchange_value( $value );
		}
		return $value;
	}

	/**
	 * Get option from gateway
	 *
	 * @param WC_Shipping_Flat_Rate $gateway Flat rate gateway.
	 *
	 * @return array
	 */
	public static function get_options_from_gateway( WC_Shipping_Flat_Rate $gateway ) {
		$woocommerce = WooCommerce::instance();

		if ( version_compare( $woocommerce->version, '2.4.0', '>=' ) ) {
			return explode( "\n", $gateway->settings['options'] );
		} else {
			return $gateway->options;
		}
	}

	/**
	 * Generate flat rate from title
	 *
	 * @param string $title Title.
	 *
	 * @return string
	 */
	public static function generate_flat_id_from_title( $title ) {
		return ( sanitize_title( trim( $title ) ) );
	}

	/**
	 * Calculate cart
	 *
	 * @param WC_Cart $cart Cart.
	 *
	 * @return void
	 */
	public function woocommerce_cart_calculate_fees( WC_Cart $cart ) {
		global $woocommerce;
		$ap_options_fees = get_option( 'woocommerce_activepayments_options_fees', array() );

		$current_gateway = $woocommerce->session->chosen_payment_method;

		$avaliable_gateways = $woocommerce->payment_gateways()->get_available_payment_gateways();

		if ( ! in_array( $current_gateway, array_keys( $avaliable_gateways ), true ) ) {
			$current_gateway = '';
			foreach ( $avaliable_gateways as $gateway_key => $gateway_val ) {
				$current_gateway = $gateway_key;
				break;
			}
		}
		if (
			isset( $ap_options_fees[ $current_gateway ] ) &&
			isset( $ap_options_fees[ $current_gateway ]['enabled'] ) &&
			'1' === $ap_options_fees[ $current_gateway ]['enabled']
		) {
			$taxable    = true;
			$calc_taxes = get_option( 'woocommerce_calc_taxes' ) === 'yes' ? true : false;
			if ( ! $calc_taxes ) {
				$taxable = false;
			}
			$tax_class = '-none-';
			if ( isset( $ap_options_fees[ $current_gateway ]['tax_class'] ) ) {
				$tax_class = $ap_options_fees[ $current_gateway ]['tax_class'];
			}
			if ( '-none-' === $tax_class ) {
				$taxable = false;
			}

			$title = !empty($ap_options_fees[ $current_gateway ]['title'])? $ap_options_fees[ $current_gateway ]['title'] : '';
			if ( function_exists( 'icl_t' ) ) {
				$title = icl_t('woocommerce_activepayments', 'title-'.$current_gateway, $title);
			}


			$amount             = $ap_options_fees[ $current_gateway ]['amount'];
			$type               = $ap_options_fees[ $current_gateway ]['type'];
			$total_in_cart      = $cart->cart_contents_total + $cart->shipping_total;
			$prices_include_tax = get_option( 'woocommerce_prices_include_tax' ) === 'yes' ? true : false;

			if ( $calc_taxes && $prices_include_tax ) {
				$taxes = $cart->get_taxes();
				foreach ( $taxes as $tax_value ) {
					$total_in_cart = $total_in_cart + $tax_value;
				}

			}
			$min_order_total = $ap_options_fees[ $current_gateway ]['min_order_total'];
			if ( '' !== $min_order_total ) {
				$min_order_total = apply_filters( 'wpdesk_value_in_currency', floatval( $min_order_total ) );
			} else {
				$min_order_total = 0;
			}
			$max_order_total = $ap_options_fees[ $current_gateway ]['max_order_total'];
			if ( '' !== $max_order_total ) {
				$max_order_total = apply_filters( 'wpdesk_value_in_currency', floatval( $max_order_total ) );
			}
			if ( $total_in_cart >= $min_order_total && ( '' === $max_order_total || $total_in_cart <= $max_order_total ) ) {
				if ( '' !== $amount && '0' !== $amount ) {
					$amount = floatval( $amount );
					if ( 'fixed' === $type ) {
						$fee = apply_filters( 'wpdesk_value_in_currency', $amount );
					}
					if ( 'percent' === $type ) {
						$precision = get_option( 'woocommerce_price_num_decimals', 2 );
						$fee       = apply_filters( 'wpdesk_value_in_currency', round( $total_in_cart * $amount / 100, $precision ) );
					}



					if ( $taxable ) {
						$cart->add_fee( $title, $fee, $taxable, $tax_class );
					} else {
						$cart->add_fee( $title, $fee, $taxable );
					}
					$woocommerce->session->set( 'active_payments_fee', sanitize_title( $title ) );
				}
			}
		}
	}

	/**
	 * Filter active gateways
	 *
	 * @param array $gateways Gateways.
	 *
	 * @return array
	 */
	public function filter_active_gateways( array $gateways ) {
		$order_id = absint( get_query_var( 'order-pay' ) );
		if ( $this->is_without_any_settings || is_null ( WC()->cart ) ) {
			return $gateways;
		}
		global $woocommerce;

		$sum = WC()->cart->get_subtotal();
		if ( wc_prices_include_tax() ) {
			$sum += WC()->cart->get_subtotal_tax();
		}

		if( $order_id ) {
			$order = new WC_Order( $order_id );
			$sum = $order->get_total();
		}

		$shipping_method = $this->get_shipping_method( $woocommerce );

		if ( ! empty( $shipping_method ) ) {

			if ( version_compare( $woocommerce->version, '2.6', '>=' ) ) {
				if ( strpos( $shipping_method, 'flexible_shipping_ups' ) === 0 ) {
					$shipping_method = $this->get_shipping_method_for_flexible_shipping_ups( $shipping_method );
				}
				$ap_options = $this->settings;
				foreach ( $gateways as $gateway_id => $gateway ) {
					if ( 'apaczka' === $shipping_method || 'apaczka_cod' === $shipping_method ) {
						if ( ! isset( $ap_options[ $shipping_method . ':0' ][ $gateway_id ] ) || 0 === $ap_options[ $shipping_method . ':0' ][ $gateway_id ] ) {
							unset( $gateways[ $gateway_id ] );
						}
					} elseif ( ! isset( $ap_options[ $shipping_method ][ $gateway_id ] ) || 0 === $ap_options[ $shipping_method ][ $gateway_id ] ) {
						if ( ! isset( $ap_options[ $shipping_method . ':0' ][ $gateway_id ] ) || 0 === $ap_options[ $shipping_method . ':0' ][ $gateway_id ] ) {
							unset( $gateways[ $gateway_id ] );
						}
					}

					if ( isset( $ap_options[ $gateway_id ] ) && isset( $ap_options[ $gateway_id ]['amount'] ) && '' !== $ap_options[ $gateway_id ]['amount'] && floatval( $ap_options[ $gateway_id ]['amount'] ) < $sum ) {
						unset( $gateways[ $gateway_id ] );
					}
				}
				return $gateways;
			} else {
				$new_gateways = array();

				$flat        = strpos( $shipping_method, 'flat_rate' ) !== false;
				$shipping_fs = false;

				if ( $flat ) {
					$shipping_method = explode( ':', $shipping_method );
					$shipping_table  = false;
				} else {
					$shipping_table = strpos( $shipping_method, 'table_rate_shipping' ) !== false;
					$shipping_fs    = strpos( $shipping_method, 'flexible_shipping' ) !== false;

				}

				foreach ( $gateways as $gateway_id => $gateway ) {
					if ( $flat ) {
						if ( isset( $shipping_method[1] ) && '' !== $shipping_method[1] ) {
							$shipping_method[1] = self::generate_flat_id_from_title( $shipping_method[1] );
							$md5                = $shipping_method[0] . '_' . $gateway_id . '_' . $shipping_method[1];
						} else {
							$md5 = str_replace( '-', '_', $shipping_method[0] ) . '_' . $gateway_id;
						}
					} elseif ( $shipping_table ) {
						$md5 = 'table_rate_shipping_' . $gateway_id . '_' . str_replace( array( 'table-rate-shipping-', 'table_rate_shipping_' ), '', $shipping_method );
					} elseif ( $shipping_fs ) {
						$md5 = 'flexible_shipping_' . $gateway_id . '_' . $shipping_method;
					} else {
						$md5 = str_replace( '-', '_', $shipping_method ) . '_' . $gateway_id;
					}

					$amount = $this->plugin->get_setting_value( 'pm_' . md5( $gateway_id . '_amount' ) );

					$setting       = 'pm_' . md5( $md5 );
					$setting_value = $this->plugin->get_setting_value( $setting );

					if ( ! empty( $setting_value ) ) {
						if ( empty( $amount ) || ( ! empty( $amount ) && $sum < $amount ) ) {
							$new_gateways[ $gateway_id ] = $gateway;
						}
					}
				}

				return $new_gateways;
			}
		} else {
			return $gateways;
		}

	}

	/**
	 * Returns selected shipping method.
	 *
	 * @param WooCommerce $woocommerce .
	 *
	 * @return string;
	 *
	 */
	private function get_shipping_method( $woocommerce ) {
		if ( version_compare( $woocommerce->version, '2.1.0', '>=' ) ) {
			$shipping_method = @reset( $woocommerce->session->get( 'chosen_shipping_methods' ) );
		} else {
			$shipping_method = $woocommerce->sesssion->chosen_shipping_method;
		}

		$shipping_method = empty( $shipping_method ) ? '' : $shipping_method;

		/**
		 * Returns shipping method.
		 *
		 * Method should be processed by shipping integrations to get right shipping method used in Active Payments settings.
		 * Used by WP Desk Live Rates plugins, which creates shipping rates from rates returned from API.
		 *
		 * @param string $shipping_method Shipping method selected in checkout.
		 * @return string Converted shipping method (if match by WP Desk Live Rates plugin) or original shipping method selected in checkout.
		 */
		return apply_filters( 'woocommerce_active_payments_checkout_shipping_method', $shipping_method );
	}

	/**
	 * Get shipping method for Flexible Shipping UPS shipping method.
	 * Legacy function for old FS UPS versions (without woocommerce_active_payments_checkout_shipping_method filter).
	 *
	 * @param string $shipping_method .
	 *
	 * @return string
	 *
	 */
	private function get_shipping_method_for_flexible_shipping_ups( $shipping_method ) {
		$flexible_shipping_ups_parts = explode( self::SHIPPING_RATE_DELIMITER, $shipping_method );
		if ( self::SHIPPING_RATE_FULL_NAME_ALL_PARTS_COUNT === count( $flexible_shipping_ups_parts ) ) {
			$shipping_method = $flexible_shipping_ups_parts[ self::SHIPPING_METHOD_NAME_PART ]
			                   . self::SHIPPING_RATE_DELIMITER
			                   . $flexible_shipping_ups_parts[ self::SHIPPING_ZONE_ID_PART ]
			;
		}

		return $shipping_method;
	}

}



