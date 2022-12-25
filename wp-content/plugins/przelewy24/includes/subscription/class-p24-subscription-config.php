<?php
/**
 * File that define P24_Subscription_Config class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class P24_Subscription_Config
 */
class P24_Subscription_Config {


	/**
	 * Days to renew.
	 *
	 * @return int
	 */
	public static function days_to_renew() {
		$ret = get_option( 'przelewy24_subscriptions_days' );
		if ( ! isset( $ret ) ) {
			return 1;
		} else {
			return (int) $ret;
		}
	}

	/**
	 * Is active.
	 *
	 * @return bool
	 */
	public static function is_active() {
		$ret = get_option( 'przelewy24_subscriptions_active' );
		if ( ! isset( $ret ) ) {
			return false;
		} else {
			return 'yes' === $ret;
		}
	}

	/**
	 * Get page id.
	 *
	 * @return int|null
	 */
	public static function page_id() {
		$page_id = (int) get_option( 'p24_subscription_page_id', 0 );

		return $page_id ? $page_id : null;
	}

	/**
	 * Set page id.
	 *
	 * @param int|null $page_id Id of page, null to unset.
	 */
	public static function set_page_id( $page_id ) {
		$page_id = (int) $page_id;
		$page_id = $page_id ? $page_id : null;
		update_option( 'p24_subscription_page_id', $page_id );
	}

	/**
	 * Set page for user.
	 */
	public static function set_page() {
		$page_id = self::page_id();
		if ( $page_id ) {
			$subscription_page = get_post( $page_id );
			if ( ! $subscription_page ) {
				$page_id = null;
			}
		}
		if ( ! $page_id ) {
			$page_id = wp_insert_post(
				array(
					'post_title'   => 'P24 Subskrypcje',
					'post_name'    => 'p24-subscription',
					'post_type'    => 'page',
					'post_content' => '<!-- wp:shortcode -->[p24_user_subscription]<!-- /wp:shortcode -->',
					'post_status'  => 'publish',
				)
			);
			self::set_page_id( $page_id );
		}

		return $page_id;
	}

	/**
	 * Update page.
	 *
	 * @param string $action Name of action.
	 */
	private static function update_page( $action ) {
		switch ( $action ) {
			case 'delete':
				self::set_page_id( null );
				break;
			case 'force':
				self::set_page_id( null );
				self::set_page();
				break;
			case 'generate':
				self::set_page();
				break;
			case 'nothing':
			default:
				break;
		}
	}

	/**
	 * Update config.
	 *
	 * @param array $data Provided data.
	 * @return array
	 */
	public static function update_config( $data ) {
		if ( array_key_exists( 'p24_subscriptions_active', $data ) ) {
			$active = (bool) $data['p24_subscriptions_active'];
		} else {
			$active = false;
		}
		if ( array_key_exists( 'p24_subscriptions_days', $data ) ) {
			$days = (int) $data['p24_subscriptions_days'];
		}
		if ( ! isset( $days ) || $days < 1 ) {
			$days = 1;
		}
		if ( array_key_exists( 'p24_subscription_page', $data ) ) {
			self::update_page( $data['p24_subscription_page'] );
		}
		$ret = array(
			'przelewy24_subscriptions_active' => $active ? 'yes' : 'no',
			'przelewy24_subscriptions_days'   => $days,
		);

		return $ret;
	}
}
