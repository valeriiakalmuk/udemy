<?php
/**
 * File that define P24_Status_Decorator_Db class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class to help decorate statuses with access to a database.
 */
class P24_Status_Decorator_Db {

	/**
	 * Escape strings in array and make it in comma separated list.
	 *
	 * @param array $input Array of string.
	 * @return string
	 */
	private static function implode_strings( $input ) {
		$mapper = function ( $in ) {
			return "'" . esc_sql( $in ) . "'";
		};
		$mapped = array_map( $mapper, $input );

		return implode( ', ', $mapped );
	}

	/**
	 * Cancel unpaid orders that has custom Przelewy24 status.
	 *
	 * @param string $pending_status Status for pending Przelewy24 payments.
	 */
	public static function cancel_unpaid_p24_orders( $pending_status ) {
		$held_duration = (int) get_option( P24_Woo_Commerce_Internals::HOLD_STOCK_MINUTES );

		/* We have to copy these two conditions from wc_cancel_unpaid_orders function. */
		if ( $held_duration < 1 || 'yes' !== get_option( P24_Woo_Commerce_Internals::MANAGE_STOCK ) ) {
			return;
		}

		$date               = new DateTime( $held_duration . ' minutes ago', wp_timezone() );
		$escaped_post_types = self::implode_strings( wc_get_order_types() );

		/*
		 * The code below is formatted after WC_Order_Data_Store_CPT::get_unpaid_orders.
		 * It is not possible to make it follow standards.
		 */

		// @codingStandardsIgnoreStart
		global $wpdb;
		$unpaid_orders = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT posts.ID
				FROM    {$wpdb->posts} AS posts
				WHERE   posts.post_type   IN ({$escaped_post_types})
				AND     posts.post_status = '%s'
				AND     posts.post_modified < %s",
				$pending_status,
				$date->format( 'Y-m-d H:i:s' )
			)
		);
		// @codingStandardsIgnoreEnd

		/* The code fragment below is copied from wc_cancel_unpaid_orders function. */
		if ( $unpaid_orders ) {
			foreach ( $unpaid_orders as $unpaid_order ) {
				$order = wc_get_order( $unpaid_order );

				if ( apply_filters( 'woocommerce_cancel_unpaid_order', 'checkout' === $order->get_created_via(), $order ) ) {
					$order->update_status( 'cancelled', __( 'Unpaid order cancelled - time limit reached.', 'woocommerce' ) );
				}
			}
		}
	}

}
