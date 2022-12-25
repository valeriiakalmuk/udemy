<?php
/**
 * File that define P24_Leaderboards_Controller class. This is code from WooCommerce plugin with overwritten data
 * stores. Slightly rebuilt (few texts were moved to constants).
 *
 * @package Przelewy24
 */

use Automattic\WooCommerce\Admin\API\Leaderboards;
use P24_Categories_Data_Store as CategoriesDataStore;
use P24_Coupons_Data_Store as CouponsDataStore;
use P24_Customers_Data_Store as CustomersDataStore;
use P24_Products_Data_Store as ProductsDataStore;

/**
 * Class P24_Leaderbords_Controller.
 */
class P24_Leaderboards_Controller extends Leaderboards {
	const LEADERBOARD_ARGUMENT_ORDER_BY      = 'orderby';
	const LEADERBOARD_ARGUMENT_ORDER         = 'order';
	const LEADERBOARD_ARGUMENT_AFTER         = 'after';
	const LEADERBOARD_ARGUMENT_BEFORE        = 'before';
	const LEADERBOARD_ARGUMENT_PER_PAGE      = 'per_page';
	const LEADERBOARD_ARGUMENT_EXTENDED_INFO = 'extended_info';
	const LEADERBOARD_ARGUMENT_ORDER_AFTER   = 'order_after';
	const LEADERBOARD_ARGUMENT_ORDER_BEFORE  = 'order_before';

	/**
	 * Get the data for the coupons leaderboard.
	 *
	 * @param int    $per_page Number of rows.
	 * @param string $after Items after date.
	 * @param string $before Items before date.
	 * @param string $persisted_query URL query string.
	 *
	 * @return array
	 */
	public function get_coupons_leaderboard( $per_page, $after, $before, $persisted_query ) {
		$coupons_data_store = new CouponsDataStore();
		$coupons_data       = $per_page > 0 ? $coupons_data_store->get_data(
			array(
				self::LEADERBOARD_ARGUMENT_ORDER_BY      => 'orders_count',
				self::LEADERBOARD_ARGUMENT_ORDER         => 'desc',
				self::LEADERBOARD_ARGUMENT_AFTER         => $after,
				self::LEADERBOARD_ARGUMENT_BEFORE        => $before,
				self::LEADERBOARD_ARGUMENT_PER_PAGE      => $per_page,
				self::LEADERBOARD_ARGUMENT_EXTENDED_INFO => true,
			)
		)->data : array();

		$rows = array();
		foreach ( $coupons_data as $coupon ) {
			$url_query   = wp_parse_args(
				array(
					'filter'  => 'single_coupon',
					'coupons' => $coupon['coupon_id'],
				),
				$persisted_query
			);
			$coupon_url  = wc_admin_url( '/analytics/coupons', $url_query );
			$coupon_code = isset( $coupon['extended_info'] ) && isset( $coupon['extended_info']['code'] ) ? $coupon['extended_info']['code'] : '';
			$rows[]      = array(
				array(
					'display' => "<a href='{$coupon_url}'>{$coupon_code}</a>",
					'value'   => $coupon_code,
				),
				array(
					'display' => wc_admin_number_format( $coupon['orders_count'] ),
					'value'   => $coupon['orders_count'],
				),
				array(
					'display' => wc_price( $coupon['amount'] ),
					'value'   => $coupon['amount'],
				),
			);
		}

		return array(
			'id'      => 'coupons',
			'label'   => __( 'Top Coupons - Number of Orders', 'woocommerce' ),
			'headers' => array(
				array(
					'label' => __( 'Coupon Code', 'woocommerce' ),
				),
				array(
					'label' => __( 'Orders', 'woocommerce' ),
				),
				array(
					'label' => __( 'Amount Discounted', 'woocommerce' ),
				),
			),
			'rows'    => $rows,
		);
	}

	/**
	 * Get the data for the categories leaderboard.
	 *
	 * @param int    $per_page Number of rows.
	 * @param string $after Items after date.
	 * @param string $before Items before date.
	 * @param string $persisted_query URL query string.
	 *
	 * @return array
	 */
	public function get_categories_leaderboard( $per_page, $after, $before, $persisted_query ) {
		$categories_data_store = new CategoriesDataStore();
		$categories_data       = $per_page > 0 ? $categories_data_store->get_data(
			array(
				self::LEADERBOARD_ARGUMENT_ORDER_BY      => 'items_sold',
				self::LEADERBOARD_ARGUMENT_ORDER         => 'desc',
				self::LEADERBOARD_ARGUMENT_AFTER         => $after,
				self::LEADERBOARD_ARGUMENT_BEFORE        => $before,
				self::LEADERBOARD_ARGUMENT_PER_PAGE      => $per_page,
				self::LEADERBOARD_ARGUMENT_EXTENDED_INFO => true,
			)
		)->data : array();

		$rows = array();
		foreach ( $categories_data as $category ) {
			$url_query     = wp_parse_args(
				array(
					'filter'     => 'single_category',
					'categories' => $category['category_id'],
				),
				$persisted_query
			);
			$category_url  = wc_admin_url( '/analytics/categories', $url_query );
			$category_name = isset( $category['extended_info'] ) && isset( $category['extended_info']['name'] ) ? $category['extended_info']['name'] : '';
			$rows[]        = array(
				array(
					'display' => "<a href='{$category_url}'>{$category_name}</a>",
					'value'   => $category_name,
				),
				array(
					'display' => wc_admin_number_format( $category['items_sold'] ),
					'value'   => $category['items_sold'],
				),
				array(
					'display' => wc_price( $category['net_revenue'] ),
					'value'   => $category['net_revenue'],
				),
			);
		}

		return array(
			'id'      => 'categories',
			'label'   => __( 'Top Categories - Items Sold', 'woocommerce' ),
			'headers' => array(
				array(
					'label' => __( 'Category', 'woocommerce' ),
				),
				array(
					'label' => __( 'Items Sold', 'woocommerce' ),
				),
				array(
					'label' => __( 'Net Sales', 'woocommerce' ),
				),
			),
			'rows'    => $rows,
		);
	}

	/**
	 * Get the data for the customers leaderboard.
	 *
	 * @param int    $per_page Number of rows.
	 * @param string $after Items after date.
	 * @param string $before Items before date.
	 * @param string $persisted_query URL query string.
	 *
	 * @return array
	 */
	public function get_customers_leaderboard( $per_page, $after, $before, $persisted_query ) {
		$customers_data_store = new CustomersDataStore();
		$customers_data       = $per_page > 0 ? $customers_data_store->get_data(
			array(
				self::LEADERBOARD_ARGUMENT_ORDER_BY     => 'total_spend',
				self::LEADERBOARD_ARGUMENT_ORDER        => 'desc',
				self::LEADERBOARD_ARGUMENT_ORDER_AFTER  => $after,
				self::LEADERBOARD_ARGUMENT_ORDER_BEFORE => $before,
				self::LEADERBOARD_ARGUMENT_PER_PAGE     => $per_page,
			)
		)->data : array();

		$rows = array();
		foreach ( $customers_data as $customer ) {
			$url_query    = wp_parse_args(
				array(
					'filter'    => 'single_customer',
					'customers' => $customer['id'],
				),
				$persisted_query
			);
			$customer_url = wc_admin_url( '/analytics/customers', $url_query );
			$rows[]       = array(
				array(
					'display' => "<a href='{$customer_url}'>{$customer['name']}</a>",
					'value'   => $customer['name'],
				),
				array(
					'display' => wc_admin_number_format( $customer['orders_count'] ),
					'value'   => $customer['orders_count'],
				),
				array(
					'display' => wc_price( $customer['total_spend'] ),
					'value'   => $customer['total_spend'],
				),
			);
		}

		return array(
			'id'      => 'customers',
			'label'   => __( 'Top Customers - Total Spend', 'woocommerce' ),
			'headers' => array(
				array(
					'label' => __( 'Customer Name', 'woocommerce' ),
				),
				array(
					'label' => __( 'Orders', 'woocommerce' ),
				),
				array(
					'label' => __( 'Total Spend', 'woocommerce' ),
				),
			),
			'rows'    => $rows,
		);
	}

	/**
	 * Get the data for the products leaderboard.
	 *
	 * @param int    $per_page Number of rows.
	 * @param string $after Items after date.
	 * @param string $before Items before date.
	 * @param string $persisted_query URL query string.
	 *
	 * @return array
	 */
	public function get_products_leaderboard( $per_page, $after, $before, $persisted_query ) {
		$products_data_store = new ProductsDataStore();
		$products_data       = $per_page > 0 ? $products_data_store->get_data(
			array(
				self::LEADERBOARD_ARGUMENT_ORDER_BY      => 'items_sold',
				self::LEADERBOARD_ARGUMENT_ORDER         => 'desc',
				self::LEADERBOARD_ARGUMENT_AFTER         => $after,
				self::LEADERBOARD_ARGUMENT_BEFORE        => $before,
				self::LEADERBOARD_ARGUMENT_PER_PAGE      => $per_page,
				self::LEADERBOARD_ARGUMENT_EXTENDED_INFO => true,
			)
		)->data : array();

		$rows = array();
		foreach ( $products_data as $product ) {
			$url_query    = wp_parse_args(
				array(
					'filter'   => 'single_product',
					'products' => $product['product_id'],
				),
				$persisted_query
			);
			$product_url  = wc_admin_url( '/analytics/products', $url_query );
			$product_name = isset( $product[ self::LEADERBOARD_ARGUMENT_EXTENDED_INFO ] )
				&& isset( $product[ self::LEADERBOARD_ARGUMENT_EXTENDED_INFO ]['name'] )
				? $product[ self::LEADERBOARD_ARGUMENT_EXTENDED_INFO ]['name'] : '';
			$rows[]       = array(
				array(
					'display' => "<a href='{$product_url}'>{$product_name}</a>",
					'value'   => $product_name,
				),
				array(
					'display' => wc_admin_number_format( $product['items_sold'] ),
					'value'   => $product['items_sold'],
				),
				array(
					'display' => wc_price( $product['net_revenue'] ),
					'value'   => $product['net_revenue'],
				),
			);
		}

		return array(
			'id'      => 'products',
			'label'   => __( 'Top Products - Items Sold', 'woocommerce' ),
			'headers' => array(
				array(
					'label' => __( 'Product', 'woocommerce' ),
				),
				array(
					'label' => __( 'Items Sold', 'woocommerce' ),
				),
				array(
					'label' => __( 'Net Sales', 'woocommerce' ),
				),
			),
			'rows'    => $rows,
		);
	}
}
