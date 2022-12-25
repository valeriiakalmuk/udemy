<?php
/**
 * File that define P24_Subscription_Db class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class P24_Subscription_Db
 */
class P24_Subscription_Db {

	/**
	 * Extend or add subscription.
	 *
	 * @param P24_No_Mc_Product_Subscription $subscription Subcription product.
	 * @param int                            $quantity Quantity of subscriptions.
	 * @param WP_User                        $user Buyer.
	 * @param WC_Order                       $order Current order.
	 */
	public static function extend_subscription( $subscription, $quantity, $user, $order ) {
		global $wpdb;
		$user_id         = (int) $user->ID;
		$subscription_id = (int) $subscription->get_id();
		/* We need a plain integer for database relations. */
		$order_id = (int) $order->get_id();
		$data     = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, valid_to FROM {$wpdb->prefix}woocommerce_p24_subscription WHERE user_id = %d AND product_id = %d;",
				array( $user_id, $subscription_id )
			)
		); /* db call ok; no cache ok */

		$now  = new DateTime();
		$days = (int) ( $subscription->get_days() * $quantity );
		if ( $data ) {
			$id       = (int) $data->id;
			$alt_date = new DateTime( "$days days" );
			try {
				$date = new DateTime( $data->valid_to );
				$date->modify( "$days days" );
				/* If the subscription is too old. */
				if ( $date < $alt_date ) {
					$date = $alt_date;
				}
			} catch ( Exception $ex ) {
				/* The best fall back we can do. */
				$date = $alt_date;
			}

			$wpdb->update(
				"{$wpdb->prefix}woocommerce_p24_subscription",
				array(
					'valid_to'      => $date->format( 'Y-m-d H:i:s' ),
					'last_order_id' => $order_id,
					'last_checked'  => $now->format( 'Y-m-d H:i:s' ),
				),
				array( 'id' => $id ), /* The NULL is not SQL compatible. */
				array( '%s', '%d', '%s' ),
				array( '%d' )
			);  /* db call ok; no cache ok */
		} else {
			$date    = new DateTime( "$days days" );
			$data    = array(
				'user_id'       => $user_id,
				'product_id'    => $subscription_id,
				'valid_to'      => $date->format( 'Y-m-d H:i:s' ),
				'extend'        => 0,
				'last_order_id' => $order_id,
				'last_checked'  => $now->format( 'Y-m-d H:i:s' ),
			);
			$formats = array( '%d', '%d', '%s', '%d', '%d', '%s' );
			$wpdb->insert( "{$wpdb->prefix}woocommerce_p24_subscription", $data, $formats ); /* db call ok */
		}

	}

	/**
	 * Add card reference.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $card_ref Card reference.
	 */
	public static function add_card_reference( $order, $card_ref ) {
		global $wpdb;
		/* We need a plain integer for database relations. */
		$last_order_id = (int) $order->get_id();
		$now           = new DateTime();
		$wpdb->update(
			"{$wpdb->prefix}woocommerce_p24_subscription",
			array(
				'extend'       => 1,
				'card_ref'     => $card_ref,
				'last_checked' => $now->format( 'Y-m-d H:i:s' ),
			),
			array( 'last_order_id' => $last_order_id ), /* The NULL is not SQL compatible. */
			array( '%d', '%s', '%s' ),
			array( '%d' )
		);  /* db call ok; no cache ok */
	}

	/**
	 * Update card reference.
	 *
	 * @param int    $subcription_id Subcription id.
	 * @param string $card_ref Card reference.
	 */
	public static function update_card_reference( $subcription_id, $card_ref ) {
		global $wpdb;
		$now = new DateTime();
			$wpdb->update(
				"{$wpdb->prefix}woocommerce_p24_subscription",
				array(
					'extend'       => 1,
					'card_ref'     => $card_ref,
					'last_checked' => $now->format( 'Y-m-d H:i:s' ),
				),
				array( 'id' => (int) $subcription_id ), /* The NULL is not SQL compatible. */
				array( '%d', '%s', '%s' ),
				array( '%d' )
			);  /* db call ok; no cache ok */
	}

	/**
	 * Clear card reference.
	 *
	 * @param int $subcription_id Subcription id.
	 */
	public static function clear_card_reference( $subcription_id ) {
		global $wpdb;
			$wpdb->update(
				"{$wpdb->prefix}woocommerce_p24_subscription",
				array(
					'extend'   => 0,
					'card_ref' => null,
				),
				array( 'id' => (int) $subcription_id ), /* The NULL is not SQL compatible. */
				array( '%d', '%s' ),
				array( '%d' )
			);  /* db call ok; no cache ok */
	}

	/**
	 * End subscription.
	 *
	 * @param int $subscription_id Subscription id.
	 * @return bool True on success.
	 */
	public static function end_subscription( $subscription_id ) {
		global $wpdb;
		$yesterday = new DateTime( 'yesterday' );
		$ok        = $wpdb->update(
			"{$wpdb->prefix}woocommerce_p24_subscription",
			array(
				'extend'   => 0,
				'card_ref' => null,
				'valid_to' => $yesterday->format( 'Y-m-d H:i:s' ),
			),
			array( 'id' => (int) $subscription_id ), /* The NULL is not SQL compatible. */
			array( '%d', '%s', '%s' ),
			array( '%d' )
		);  /* db call ok; no cache ok */

		return (bool) $ok;
	}

	/**
	 * Get active list.
	 *
	 * @return array|object|null
	 */
	public static function get_active_list() {
		global $wpdb;
		$now  = new DateTime();
		$list = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT
						u.user_nicename,
						u.user_email,
						s.valid_to,
						p.post_title AS subscription_title,
					    s.id AS record_id
					FROM {$wpdb->prefix}woocommerce_p24_subscription AS s
					INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = s.product_id
					INNER JOIN {$wpdb->prefix}users AS u ON u.ID = s.user_id
					WHERE s.valid_to > %s
					ORDER BY p.post_title, u.user_email, s.id
					;
				",
				array( $now->format( 'Y-m-d H:i:s' ) )
			)
		); /* db call ok; no cache ok */

		return $list;
	}

	/**
	 * Get inactive list.
	 *
	 * @return array|object|null
	 */
	public static function get_inactive_list() {
		global $wpdb;
		$now  = new DateTime();
		$list = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT
						u.user_nicename,
						u.user_email,
						s.valid_to,
						p.post_title AS subscription_title
					FROM {$wpdb->prefix}woocommerce_p24_subscription AS s
					INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = s.product_id
					INNER JOIN {$wpdb->prefix}users AS u ON u.ID = s.user_id
					WHERE s.valid_to < %s
					ORDER BY p.post_title, u.user_email, s.id
					;
				",
				array( $now->format( 'Y-m-d H:i:s' ) )
			)
		); /* db call ok; no cache ok */

		return $list;
	}

	/**
	 * Get active list for user.
	 *
	 * @return array|object|null
	 * @param WP_User $user User of interest.
	 */
	public static function get_active_list_for_user( $user ) {
		global $wpdb;
		$user_id = $user->ID;
		$now     = new DateTime();
		$list    = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT
						s.id AS record_id,
						s.valid_to,
						s.card_ref,
						p.ID AS product_id,
						p.post_title AS subscription_title
					FROM {$wpdb->prefix}woocommerce_p24_subscription AS s
					INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = s.product_id
					WHERE s.valid_to > %s
					AND s.user_id = %d
					ORDER BY p.post_title, s.id
					;
				",
				array( $now->format( 'Y-m-d H:i:s' ), $user_id )
			)
		); /* db call ok; no cache ok */

		return $list;
	}

	/**
	 * Get inactive list for user.
	 *
	 * @return array|object|null
	 * @param WP_User $user User of interest.
	 */
	public static function get_inactive_list_for_user( $user ) {
		global $wpdb;
		$user_id = $user->ID;
		$now     = new DateTime();
		$list    = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT
						s.id AS record_id,
						p.ID AS product_id,
						p.post_title AS subscription_title
					FROM {$wpdb->prefix}woocommerce_p24_subscription AS s
					INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = s.product_id
					WHERE s.valid_to < %s
					AND s.user_id = %d
					ORDER BY p.post_title, s.id
					;
				",
				array( $now->format( 'Y-m-d H:i:s' ), $user_id )
			)
		); /* db call ok; no cache ok */

		return $list;
	}

	/**
	 * Mark checked.
	 *
	 * @param int $id Id of subscription.
	 */
	public static function mark_checked( $id ) {
		global $wpdb;
		$id  = (int) $id;
		$now = new DateTime();
		$wpdb->update(
			"{$wpdb->prefix}woocommerce_p24_subscription",
			array(
				'last_checked' => $now->format( 'Y-m-d H:i:s' ),
			),
			array( 'id' => $id ), /* The NULL is not SQL compatible. */
			array( '%s' ),
			array( '%d' )
		);  /* db call ok; no cache ok */
	}

	/**
	 * Find_subscription_to_extends.
	 *
	 * @param DateTimeInterface $critical_date The date of subscription.
	 */
	public static function find_subscription_to_extends( $critical_date ) {
		global $wpdb;
		$critical_date_string = $critical_date->format( 'Y-m-d H:i:s' );
		$cool_down            = new DateTime( '- 1 hour' );
		$cool_down_string     = $cool_down->format( 'Y-m-d H:i:s' );
		$data                 = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT id, product_id, user_id, last_order_id, card_ref
					FROM {$wpdb->prefix}woocommerce_p24_subscription
					WHERE valid_to < %s AND extend <> 0 AND card_ref IS NOT NULL AND last_checked < %s
					;
				",
				array( $critical_date_string, $cool_down_string )
			)
		); /* db call ok; no cache ok */

		return $data;
	}

	/**
	 * Get extended data for single record.
	 *
	 * @param int $subscription_id Subsription id.
	 * @return array|null
	 */
	public static function get_extended_data_for_single( $subscription_id ) {
		$subscription_id = (int) $subscription_id;
		global $wpdb;
		$data = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT
						u.user_nicename,
						u.user_email,
						p.post_title AS subscription_title
					FROM {$wpdb->prefix}woocommerce_p24_subscription AS s
					INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = s.product_id
					INNER JOIN {$wpdb->prefix}users AS u ON u.ID = s.user_id
					WHERE s.id = %d
					;
				",
				array( $subscription_id )
			)
		); /* db call ok; no cache ok */

		if ( $data ) {
			return (array) reset( $data );
		} else {
			return null;
		}
	}
}
