<?php
/**
 * File that define P24_Context_Provider class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class with methods allowing user to determine context this script was run from.
 */
class P24_Context_Provider {
	/**
	 * Is this reports context.
	 *
	 * @return bool
	 */
	public function is_reports_context() {
		return $this->is_dashboard_context() || $this->is_analytics_context();
	}

	/**
	 * Is wc analytics context.
	 *
	 * @return bool
	 */
	public function is_analytics_context() {
		if ( isset( $_GET['rest_route'] )
			&& 0 === strpos( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ), '/wc-analytics/' ) ) {
			return true;
		}

		return $this->is_admin_context()
			&& isset( $_GET['path'] )
			&& 0 === strpos( sanitize_text_field( wp_unslash( $_GET['path'] ) ), '/analytics/' );
	}

	/**
	 * Is this script launched from wc dashboard context.
	 *
	 * @return bool
	 */
	public function is_dashboard_context() {
		return $this->is_admin_context() && ! isset( $_GET['path'] );
	}

	/**
	 * Is this script launched from wc admin context.
	 *
	 * @return bool
	 */
	public function is_admin_context() {
		return isset( $_GET['page'] ) && 'wc-admin' === sanitize_text_field( wp_unslash( $_GET['page'] ) );
	}
}
