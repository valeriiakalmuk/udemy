<?php
/**
 * Customers data store.
 *
 * @package Przelewy24
 */

use Automattic\WooCommerce\Admin\API\Reports\Customers\DataStore;
use Automattic\WooCommerce\Admin\API\Reports\Orders\DataStore as OrdersDataStore;

/**
 * Class P24_Customers_Data_Store.
 */
class P24_Customers_Data_Store extends DataStore {
	/**
	 * Updates the database query with parameters used for Customers report: categories and order status.
	 *
	 * @param array $query_args Query arguments supplied by the user.
	 */
	protected function add_sql_query_params( $query_args ) {
		parent::add_sql_query_params( $query_args );
		$query_statement = $this->subquery->get_query_statement();
		$order_table     = OrdersDataStore::get_db_table_name();
		if ( false === strpos( $query_statement, $order_table ) ) {
			return;
		}
		$metadata_alias = 'p24_postmeta';
		$metadata_join  = P24_Multi_Currency::get_currency_filter_for_reports( $order_table );
		$this->subquery->add_sql_clause( 'left_join', $metadata_join );
		$this->subquery->add_sql_clause(
			'where',
			"AND {$metadata_alias}.meta_key = '_order_currency'"
		);
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
