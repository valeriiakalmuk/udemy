<?php

/**
 * Class ActivePaymentsAdmin
 */
class ActivePaymentsAdmin {

	/**
	 * Has settings
	 *
	 * @var bool
	 */
	protected $is_without_any_settings;

	/**
	 * Plugin.
	 *
	 * @var WPDesk_Active_Payments_Plugin
	 */
	private $plugin;

	/**
	 * ActivePaymentsAdmin constructor.
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
		add_action( 'admin_init', array( $this, 'update_settings_action' ) );
		add_action( 'init', array( $this, 'init_wpml' ) );
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 70 );
		add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen_ids' ) );

	}


	/**
	 * Add Active Payments Page to WooCommerce Screen IDs (enqueues Woo CSS & JS)
	 *
	 * @param array $screen_ids Screens ID.
	 *
	 * @return array
	 */
	public function woocommerce_screen_ids( $screen_ids ) {
		$screen_ids[] = 'woocommerce_page_woocommerce_activepayments';

		return $screen_ids;
	}

	/**
	 * Update settings
	 */
	public function update_settings_action() {
		if (
			isset( $_POST[ $this->plugin->get_namespace() ] )
			&& wp_verify_nonce( sanitize_key( $_POST[ $this->plugin->get_namespace() ] ), 'save_settings' )
		) {

			if ( isset( $_POST['option_page'] ) ) {

				if ( 'woocommerce_activepayments_settings' === $_POST['option_page'] ) {
					$plugin_woocommerce          = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
					$woocommerce_version_compare = version_compare( $plugin_woocommerce['Version'], '2.6' );

					$payment          = new WC_Payment_Gateways();
					$payment_gateways = $payment->payment_gateways();

					if ( $woocommerce_version_compare >= 0 ) {
						if ( isset( $_POST['payment_method'] ) ) {
							update_option( 'woocommerce_activepayments_options', wp_unslash( $_POST['payment_method'] ) );
						}
					} else {
						$shipping         = new WC_Shipping();
						$shipping_methods = $shipping->load_shipping_methods();

						foreach ( $shipping_methods as $method ) {
							foreach ( $payment_gateways as $payment ) {
								update_option( 'woocommerce_activepayments_pm_' . md5( $method->id . '_' . $payment->id ), ! empty( $_POST['payment_method'][ $method->id ][ $payment->id ] ) );
							}

							if ( 'flat_rate' === $method->id ) {
								$options = ActivePayments::get_options_from_gateway( $method );
								foreach ( $options as $method_option ) {
									$method_option_array = explode( '|', $method_option );
									$fname               = trim( reset( $method_option_array ) );
									$fname_id            = ActivePayments::generate_flat_id_from_title( $fname );
									foreach ( $payment_gateways as $payment ) {
										update_option(
											'woocommerce_activepayments_pm_' . md5( $method->id . '_' . $payment->id . '_' . $fname_id ),
											! empty( $_POST['payment_method'][ $method->id . ':' . $fname_id ][ $payment->id ] )
										);
									}
								}
							}

							if ( 'table_rate_shipping' === $method->id ) {
								$shipping_table_methods = get_option( 'woocommerce_table_rates', null );
								foreach ( $shipping_table_methods as $st_method ) {
									foreach ( $payment_gateways as $payment ) {
										update_option(
											'woocommerce_activepayments_pm_' . md5( $method->id . '_' . $payment->id . '_' . $st_method['identifier'] ),
											! empty( $_POST['payment_method'][ $method->id . ':' . $st_method['identifier'] ][ $payment->id ] )
										);
									}
								}
							}

							if ( 'flexible_shipping' === $method->id ) {
								$shipping_fs_methods = get_option( 'flexible_shipping_rates', null );
								if ( isset( $shipping_fs_methods ) && is_array( $shipping_fs_methods ) ) {
									foreach ( $shipping_fs_methods as $st_method ) {
										foreach ( $payment_gateways as $payment ) {
											update_option(
												'woocommerce_activepayments_pm_' . md5( $method->id . '_' . $payment->id . '_' . $st_method['identifier'] ),
												! empty( $_POST['payment_method'][ $method->id . ':' . $st_method['identifier'] ][ $payment->id ] )
											);
										}
									}
								}
								$shipping_fs_methods = get_option( 'woocommerce_flexible_shipping_rates', null );
								if ( isset( $shipping_fs_methods ) && is_array( $shipping_fs_methods ) ) {
									foreach ( $shipping_fs_methods as $st_method ) {
										foreach ( $payment_gateways as $payment ) {
											update_option(
												'woocommerce_activepayments_pm_' . md5( $method->id . '_' . $payment->id . '_' . $st_method['identifier'] ),
												! empty( $_POST['payment_method'][ $method->id . ':' . $st_method['identifier'] ][ $payment->id ] )
											);
										}
									}
								}
							}

							if ( 'kurjerzy_shipping_method' === $method->id ) {
								foreach ( $payment_gateways as $payment ) {
									foreach ( $method->couriers as $courier ) {
										update_option(
											'woocommerce_activepayments_pm_' . md5( $method->id . '_' . $courier . '_' . $payment->id ),
											! empty( $_POST['payment_method'][ $method->id . '_' . $courier ][ $payment->id ] )
										);
									}
								}
							}
						}
					}

					foreach ( $payment_gateways as $payment ) {
						if ( isset( $_POST['payment_method'][ $payment->id ]['amount'] ) ) {
							update_option( 'woocommerce_activepayments_pm_' . md5( $payment->id . '_amount' ), sanitize_text_field( wp_unslash( $_POST['payment_method'][ $payment->id ]['amount'] ) ) );
						}


					}
					$this->is_without_any_settings = false;
				}

				if ( 'woocommerce_activepayments_settings_fees' === $_POST['option_page'] && isset( $_POST['payment_fees'] ) ) {
					$payment_fees = $_POST['payment_fees'];
					foreach ( $payment_fees as $key => $payment_fee ) {
						$payment_fees[ $key ]['enabled']         = isset( $payment_fees[ $key ]['enabled'] ) ? '1' : '0';
						$payment_fees[ $key ]['title']           = isset( $payment_fees[ $key ]['title'] ) ? wp_kses_post( trim( stripslashes( $payment_fees[ $key ]['title'] ) ) ) : '';
						$payment_fees[ $key ]['tax_class']       = isset( $payment_fees[ $key ]['tax_class'] ) ? wc_clean( stripslashes( $payment_fees[ $key ]['tax_class'] ) ) : '';
						$payment_fees[ $key ]['min_order_total'] = isset( $payment_fees[ $key ]['min_order_total'] ) ? wc_format_decimal( trim( stripslashes( $payment_fees[ $key ]['min_order_total'] ) ) ) : '';
						$payment_fees[ $key ]['max_order_total'] = isset( $payment_fees[ $key ]['max_order_total'] ) ? wc_format_decimal( trim( stripslashes( $payment_fees[ $key ]['max_order_total'] ) ) ) : '';
						$payment_fees[ $key ]['type']            = isset( $payment_fees[ $key ]['type'] ) ? wc_clean( stripslashes( $payment_fees[ $key ]['type'] ) ) : '';
						$payment_fees[ $key ]['amount']          = isset( $payment_fees[ $key ]['amount'] ) ? wc_format_decimal( trim( stripslashes( $payment_fees[ $key ]['amount'] ) ) ) : '';
					}
					update_option( 'woocommerce_activepayments_options_fees', $payment_fees );
				}
			}
		}
	}

	/**
	 * Add submenu page
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Active Payments', 'woocommerce_activepayments' ),
			esc_html__( 'Active Payments', 'woocommerce_activepayments' ),
			'manage_woocommerce',
			$this->plugin->get_namespace(),
			array( $this, 'render_active_payments_page' )
		);
	}

	/**
	 * Render views for sudmenu page
	 */
	public function render_active_payments_page() {
		$tabs        = array(
			'shipping' => array(
				'page'  => 'admin.php?page=woocommerce_activepayments&tab=shipping',
				'title' => esc_html__( 'Shipping', 'woocommerce_activepayments' ),
			),
			'fees'     => array(
				'page'  => 'admin.php?page=woocommerce_activepayments&tab=fees',
				'title' => esc_html__( 'Fees', 'woocommerce_activepayments' ),
			)
		);
		$current_tab = 'shipping';
		if ( isset( $_GET['tab'] ) ) {
			$current_tab = $_GET['tab'];
		}
		if ( $current_tab != 'shipping' && $current_tab != 'fees' ) {
			$current_tab = 'shipping';
		}
		include( 'views/tabs.php' );
		if ( $current_tab == 'shipping' ) {
			$this->renderActivePaymentsShipping();
		}
		if ( $current_tab == 'fees' ) {
			$this->renderActivePaymentsFees();
		}
		include( 'views/tabs_end.php' );
	}

	public function renderActivePaymentsShipping() {

		$shipping        = new WC_Shipping();
		$shippingMethods = $shipping->load_shipping_methods();

		$shippingMethods_all = $shippingMethods;

		unset( $shippingMethods['enadawca'] );
		unset( $shippingMethods['paczka_w_ruchu'] );

		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		if ( in_array( 'woocommerce-paczkomaty-inpost/woocommerce-paczkomaty-inpost.php', $active_plugins ) ) {
			$plugin_paczkomaty = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce-paczkomaty-inpost/woocommerce-paczkomaty-inpost.php' );

			$version_compare = version_compare( $plugin_paczkomaty['Version'], '3.0' );

			if ( $version_compare >= 0 ) {
				unset( $shippingMethods['paczkomaty_shipping_method'] );
				unset( $shippingMethods['polecony_paczkomaty_shipping_method'] );
			}
		}

		$plugin_woocommerce          = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
		$woocommerce_version_compare = version_compare( $plugin_woocommerce['Version'], '2.6' );

		$payment         = new WC_Payment_Gateways();
		$paymentGateways = $payment->payment_gateways();

		// unset unused gateways
		$unsetKeys = array();

		foreach ( $paymentGateways as $key => $gateway ) {
			if ( $gateway->enabled != 'yes' ) {
				$unsetKeys[] = $key;
			}
		}
		if ( ! empty( $unsetKeys ) ) {
			foreach ( $unsetKeys as $key ) {
				unset( $paymentGateways[ $key ] );
			}
		}

		if ( $woocommerce_version_compare >= 0 ) {

			$ap_options = get_option( 'woocommerce_activepayments_options', array() );

			$shippingMethods                      = array();
			$shippingFSMethods                    = array();
			$shippingFSMethods_woo                = array();
			$shippingTableMethods                 = array();
			$shippingZones                        = WC_Shipping_Zones::get_zones();
			$worldwide                            = new WC_Shipping_Zone( 0 );
			$shippingZones[0]                     = $worldwide->get_data();
			$shippingZones[0]['shipping_methods'] = $worldwide->get_shipping_methods();
			foreach ( $shippingZones as $shippingZoneKey => $shippingZone ) {
				if ( ! isset( $shippingZone['zone_id'] ) ) {
					$shippingZone['zone_id']           = $shippingZone['id'];
					$shippingZones[ $shippingZoneKey ] = $shippingZone;
				}
				$shippingMethods[ $shippingZone['zone_id'] ] = $shippingZone['shipping_methods'];
				foreach ( $shippingMethods[ $shippingZone['zone_id'] ] as $shipping_method ) {
					if ( $shipping_method->id == 'flexible_shipping' ) {
						$shippingFSMethods[ $shipping_method->shipping_methods_option ] = get_option( $shipping_method->shipping_methods_option, array() );
					}
				}
			}
			$shippingMethods_no_zone = array();
			foreach ( $shippingMethods_all as $key => $shipping_method ) {
				$zone_settings = false;
				foreach ( $shipping_method->supports as $supports ) {
					if ( in_array( $supports, [ 'flexible-shipping', 'shipping-zones' ] ) ) {
						$zone_settings = true;
					}
				}
				if ( ! $zone_settings ) {
					if ( ! in_array( $shipping_method->id, array(
						'paczkomaty_shipping_method',
						'polecony_paczkomaty_shipping_method',
						'enadawca',
						'paczka_w_ruchu',
						'dhl',
						'dpd',
						'furgonetka'
					) ) ) {
						if ( isset( $shipping_method->enabled ) && $shipping_method->enabled == 'yes' ) {
							$shippingMethods_no_zone[ $key ] = $shipping_method;
						}
					}
				}
			}
			if ( sizeof( $shippingMethods_no_zone ) > 0 ) {
				$shippingZones['no_zone']   = array(
					'zone_name' => esc_html__( 'Other (without shipping zone)', 'woocommerce_activepayments' ),
					'zone_id'   => 'no_zone'
				);
				$shippingMethods['no_zone'] = $shippingMethods_no_zone;
			}

			$args = array(
				'ap_options'              => $ap_options,
				'shippingZones'           => $shippingZones,
				'shippingMethods'         => $shippingMethods,
				'shippingTableMethods'    => $shippingTableMethods,
				'shippingFSMethods'       => $shippingFSMethods,
				'shippingFSMethods_woo'   => $shippingFSMethods_woo,
				'paymentGateways'         => $paymentGateways,
				'is_without_any_settings' => $this->is_without_any_settings
			);

			include( 'views/activepayments_settings_2.6.php' );
		} else {
			$shippingTableMethods = get_option( 'woocommerce_table_rates', null );

			if ( in_array( 'flexible-shipping/flexible-shipping.php', $active_plugins ) ) {
				$shippingFSMethods = get_option( 'flexible_shipping_rates', array() );
			} else {
				$shippingFSMethods = array();
			}

			if ( in_array( 'woo-flexible-shipping/flexible-shipping.php', $active_plugins ) ) {
				$shippingFSMethods_woo = get_option( 'woocommerce_flexible_shipping_rates', array() );
			} else {
				$shippingFSMethods_woo = array();
			}

			$args = array(
				'shippingMethods'         => $shippingMethods,
				'shippingTableMethods'    => $shippingTableMethods,
				'shippingFSMethods'       => $shippingFSMethods,
				'shippingFSMethods_woo'   => $shippingFSMethods_woo,
				'paymentGateways'         => $paymentGateways,
				'is_without_any_settings' => $this->is_without_any_settings
			);

			include( 'views/activepayments_settings.php' );
		}
	}

	public function renderActivePaymentsFees() {

		$payment         = new WC_Payment_Gateways();
		$paymentGateways = $payment->payment_gateways();

		$ap_options_fees = get_option( 'woocommerce_activepayments_options_fees', array() );
		include( 'views/activepayments_settings_fees.php' );

	}

	public function is_default_language() {
		global $sitepress;
		if ( $sitepress instanceof \SitePress ) {
			return $sitepress->get_current_language() == $sitepress->get_default_language();
		}
		return true;
	}

	public function ap_settings_fees_row( $ap_options_fees, $method_id ) {
		$icl_language_code = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : get_bloginfo('language');
		include( 'views/activepayments_settings_fees_row.php' );
	}

	public function init_wpml() {
		if ( function_exists( 'icl_register_string' ) ) {
			$icl_language_code = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : get_bloginfo('language');
			$settings = get_option('woocommerce_activepayments_options_fees', array() );
			foreach ( $settings as $key => $array ) {
				if ( is_array( $array ) ) {
					foreach ( $array as $key2 => $value ) {
						if ( $key2 === "title" && $value != "" ) {
							icl_register_string( 'woocommerce_activepayments', 'title-'.$key, $value, false, $icl_language_code );
						}
					}
				}
			}
		}
	}
}
