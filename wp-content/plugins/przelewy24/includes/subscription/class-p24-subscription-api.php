<?php
/**
 * File that define P24_Subscription_Api class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class P24_Subscription_Api
 */
class P24_Subscription_Api {

	/**
	 * Query db.
	 *
	 * @param int|null $for_user User id.
	 * @param int|null $subscription Subcription (product) id.
	 * @return array
	 */
	private static function query_db( $for_user, $subscription ) {
		global $wpdb;
		$now          = new DateTime();
		$for_user     = (int) $for_user;
		$subscription = (int) $subscription;
		$list         = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT
						u.ID AS user_id,
						u.user_nicename,
						u.user_email,
						s.valid_to,
						p.ID AS subscription_id,
						p.post_title AS subscription_title
					FROM {$wpdb->prefix}woocommerce_p24_subscription AS s
					INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = s.product_id
					INNER JOIN {$wpdb->prefix}users AS u ON u.ID = s.user_id
					WHERE s.valid_to > %s
						AND (u.ID = %d OR 0 = %d)
						AND (p.ID = %d OR 0 = %d)
					ORDER BY p.post_title, u.user_email, s.id
					;
				",
				array(
					$now->format( 'Y-m-d H:i:s' ),
					$for_user, /* The variable is used twice. */
					$for_user,
					$subscription, /* The variable is used twice. */
					$subscription,
				)
			)
		); /* db call ok; no cache ok */

		return $list;
	}

	/**
	 * Try serve JSON.
	 */
	public function try_serve() {
		/* User credentials should be checked and high enough. */
		wp_verify_nonce( null );
		if ( isset( $_GET['p24'] ) && isset( $_GET['subscription_get'] ) ) {
			if ( isset( $_GET['user'] ) ) {
				$for_user = (int) $_GET['user'];
			} else {
				$for_user = null;
			}
			if ( isset( $_GET['subscription'] ) ) {
				$subscription = (int) $_GET['subscription'];
			} else {
				$subscription = null;
			}
			header( 'Content-Type: application/json' );
			$list = self::query_db( $for_user, $subscription );
			echo wp_json_encode( $list );
			exit();
		}
	}

	/**
	 * Bind events.
	 */
	public function bind_core_events() {
		add_action( 'admin_init', array( $this, 'try_serve' ) );
	}
}
