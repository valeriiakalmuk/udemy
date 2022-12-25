<?php
/**
 * Orders stats data store.
 *
 * @package Przelewy24
 */

use Automattic\WooCommerce\Admin\API\Reports\Orders\Stats\DataStore;

/**
 * Class P24_Orders_Stats_Data_Store.
 */
class P24_Orders_Stats_Data_Store extends DataStore {
	/**
	 * Updates the totals and intervals database queries with parameters used for Orders report: categories, coupons and order status.
	 *
	 * @param array $query_args      Query arguments supplied by the user.
	 */
	protected function orders_stats_sql_filter( $query_args ) {

		parent::orders_stats_sql_filter( $query_args );
		$metadata_join = P24_Multi_Currency::get_currency_filter_for_reports( self::get_db_table_name() );
		$this->total_query->add_sql_clause( 'join', $metadata_join );
		$this->interval_query->add_sql_clause( 'join', $metadata_join );
	}

	/**
	 * Get cache key.
	 *
	 * @param array $params Parameters.
	 *
	 * @return string
	 */
	protected function get_cache_key( $params ) {
		return parent::get_cache_key( $params ) . '_' . P24_Multi_Currency::get_admin_reports_currency();
	}
}
