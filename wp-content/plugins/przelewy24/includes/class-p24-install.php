<?php
/**
 * File that define P24_Install class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/**
 * Install methods for Przelewy 24 plugin.
 */
class P24_Install {

	const P24_INSTALLED_VERSION = 'p24_installed_version';

	/**
	 * Update database.
	 */
	private static function update_database() {
		global $wpdb;
		dbDelta(
			'
				CREATE TABLE ' . $wpdb->prefix . 'woocommerce_p24_data (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					data_type VARCHAR(32) NOT NULL,
					data_id INT NOT NULL,
					custom_key VARCHAR(32) NOT NULL,
					custom_value TEXT,
					robik TEXT,
					INDEX search_key (data_type, data_id, custom_key),
					INDEX get_key (data_type, data_id)
				);
			'
		);

		dbDelta(
			'
				CREATE TABLE ' . $wpdb->prefix . 'woocommerce_p24_order_map (
					order_hash VARCHAR(40) UNIQUE NOT NULL PRIMARY KEY,
					order_id INT NOT NULL,
					INDEX search_hash (order_hash)
				);
			'
		);

		dbDelta(
			'
				CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'woocommerce_p24_subscription(
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					user_id BIGINT NOT NULL,
					product_id BIGINT NOT NULL,
					valid_to DATETIME NOT NULL,
					extend TINYINT NOT NULL,
					card_ref TEXT NULL,
					last_order_id BIGINT NOT NULL,
					last_checked DATETIME NOT NULL,
					INDEX (user_id),
					INDEX (product_id),
					INDEX (last_order_id),
					INDEX (valid_to)
				);
			'
		);

		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "woocommerce_p24_custom_data'" ) === $wpdb->prefix . 'woocommerce_p24_custom_data' && ! $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'woocommerce_p24_data' ) ) {
			$wpdb->query(
				'
					INSERT INTO  ' . $wpdb->prefix . 'woocommerce_p24_data
					SELECT * FROM ' . $wpdb->prefix . 'woocommerce_p24_custom_data
				'
			); /* db call ok; no cache ok */
		}
	}

	/**
	 * Update shedules.
	 */
	private static function update_shedules() {
		if ( ! wp_next_scheduled( 'p24_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'p24_daily_event' );
		}
	}

	/**
	 * Check install.
	 */
	public static function check_install() {
		$configured = get_option( self::P24_INSTALLED_VERSION );
		$actual     = P24_Core::INTERNAL_VERSION;
		if ( $configured !== $actual ) {
			self::update_database();
			self::update_shedules();
			update_option( self::P24_INSTALLED_VERSION, $actual, true );
		}

	}
}
