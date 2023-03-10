<?php

if ( ! function_exists( 'wpdesk_get_order_id' ) ) {
	function wpdesk_get_order_id( $order ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return $order->id;
		} else {
			return $order->get_id();
		}
	}
}

if ( ! function_exists( 'wpdesk_get_order_meta' ) ) {
	function wpdesk_get_order_meta( $order, $meta_key, $single = false ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			$load_order = false;
			if ( in_array( $meta_key, array( 'order_date', 'customer_note' ) ) ) {
				$load_order = true;
			}
			if ( is_numeric( $order ) && ! $load_order ) {
				if ( $meta_key == '_currency' ) {
					$meta_key = '_order_currency';
				}

				return get_post_meta( $order, $meta_key, $single );
			} else {
				switch ( $meta_key ) {
					case 'order_date':
						return $order->order_date;
					case 'customer_note':
						return $order->customer_note;
					default:
						return get_post_meta( $order->id, $meta_key, $single );
				}
			}
		} else {
			if ( is_numeric( $order ) ) {
				$order = wc_get_order( $order );
			}

			switch ( $meta_key ) {
				case '_parent_id':
					return $order->get_parent_id();
					break;
				case '_status':
					return $order->get_status();
					break;
				case '_order_currency':
				case '_currency':
					return $order->get_currency();
					break;
				case '_version':
					return $order->get_version();
					break;
				case '_prices_include_tax':
					return $order->get_prices_include_tax();
					break;
				case '_date_created':
					return date( "Y-m-d H:i:s", get_date_created()->getTimestamp() );
					break;
				case '_date_modified':
					return date( "Y-m-d H:i:s", $order->get_date_modified()->getTimestamp() );
					break;
				case '_discount_total':
					return $order->get_discount_total();
					break;
				case '_discount_tax':
					return $order->get_discount_tax();
					break;
				case '_shipping_total':
					return $order->get_shipping_total();
					break;
				case '_shipping_tax':
					return $order->get_shipping_tax();
					break;
				case '_cart_tax':
					return $order->get_cart_tax();
					break;
				case '_total':
					return $order->get_total();
					break;
				case '_total_tax':
					return $order->get_total_tax();
					break;
				case '_customer_id':
					return $order->get_customer_id();
					break;
				case '_order_key':
					return $order->get_order_key();
					break;
				case '_billing_first_name':
					return $order->get_billing_first_name();
					break;
				case '_billing_last_name':
					return $order->get_billing_last_name();
					break;
				case '_billing_company':
					return $order->get_billing_company();
					break;
				case '_billing_address_1':
					return $order->get_billing_address_1();
					break;
				case '_billing_address_2':
					return $order->get_billing_address_2();
					break;
				case '_billing_city':
					return $order->get_billing_city();
					break;
				case '_billing_state':
					return $order->get_billing_state();
					break;
				case '_billing_postcode':
					return $order->get_billing_postcode();
					break;
				case '_billing_country':
					return $order->get_billing_country();
					break;
				case '_billing_email':
					return $order->get_billing_email();
					break;
				case '_billing_phone':
					return $order->get_billing_phone();
					break;

				case '_shipping_first_name':
					return $order->get_shipping_first_name();
					break;
				case '_shipping_last_name':
					return $order->get_shipping_last_name();
					break;
				case '_shipping_company':
					return $order->get_shipping_company();
					break;
				case '_shipping_address_1':
					return $order->get_shipping_address_1();
					break;
				case '_shipping_address_2':
					return $order->get_shipping_address_2();
					break;
				case '_shipping_city':
					return $order->get_shipping_city();
					break;
				case '_shipping_state':
					return $order->get_shipping_state();
					break;
				case '_shipping_postcode':
					return $order->get_shipping_postcode();
					break;
				case '_shipping_country':
					return $order->get_shipping_country();
					break;

				case '_payment_method':
					return $order->get_payment_method();
					break;
				case '_payment_method_title':
					return $order->get_payment_method_title();
					break;
				case '_transaction_id':
					return $order->get_transaction_id();
					break;
				case '_customer_ip_address':
					return $order->get_customer_ip_address();
					break;
				case '_customer_user_agent':
					return $order->get_customer_user_agent();
					break;
				case '_created_via':
					return $order->get_created_via();
					break;
				case '_customer_note':
					return $order->get_customer_note();
					break;
				case '_completed_date':
				case '_date_completed':
					$date_completed = $order->get_date_completed();
					if ( isset( $date_completed ) ) {
						return date( "Y-m-d H:i:s", $date_completed->getTimestamp() );
					}

					return null;
					break;
				case '_date_paid':
					return $order->get_date_paid();
					break;
				case '_cart_hash':
					return $order->get_cart_hash();
					break;

				case 'order_date':
					return date( "Y-m-d H:i:s", $order->get_date_created()->getTimestamp() );
					break;

				default:
					$ret = $order->get_meta( $meta_key, $single );

					return $ret;
			}
		}
	}
}

if ( ! function_exists( 'wpdesk_update_order_meta' ) ) {
	function wpdesk_update_order_meta( $order, $meta_key, $meta_value ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			if ( is_numeric( $order ) ) {
				$order_id = $order;
			} else {
				$order_id = $order->id;
			}
			update_post_meta( $order_id, $meta_key, $meta_value );
		} else {
			if ( is_numeric( $order ) ) {
				$order_id = $order;
				$order    = wc_get_order( $order_id );
			}
			$order->update_meta_data( $meta_key, $meta_value );
			$order->save();
		}
	}
}

if ( ! function_exists( 'wpdesk_is_plugin_active' ) ) {
	function wpdesk_is_plugin_active( $plugin_file ) {
		$active_plugins = (array) get_option( 'active_plugins', [] );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', [] ) );
		}

		return in_array( $plugin_file, $active_plugins ) || array_key_exists( $plugin_file, $active_plugins );
	}
}
