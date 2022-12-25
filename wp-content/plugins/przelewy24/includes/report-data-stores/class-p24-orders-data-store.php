<?php
/**
 * Orders data store.
 *
 * @package Przelewy24
 */

use Automattic\WooCommerce\Admin\API\Reports\Orders\DataStore;

/**
 * Class P24_Orders_Data_Store.
 */
class P24_Orders_Data_Store extends DataStore {
	/**
	 * Updates the database query with parameters used for orders report: coupons and products filters.
	 *
	 * @param array $query_args Query arguments supplied by the user.
	 */
	protected function add_sql_query_params( $query_args ) {
		parent::add_sql_query_params( $query_args );
		$metadata_join = P24_Multi_Currency::get_currency_filter_for_reports( self::get_db_table_name() );
		$this->subquery->add_sql_clause( 'join', $metadata_join );
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
