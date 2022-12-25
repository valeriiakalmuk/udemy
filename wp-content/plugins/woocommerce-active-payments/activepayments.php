<?php
/**
 * Plugin Name: WooCommerce Active Payments
 * Plugin URI: https://www.wpdesk.net/products/active-payments-woocommerce/
 * Description: Allows to hide certain payment methods for selected shipping methods. Works great with <a href="https://www.wpdesk.net/products/flexible-shipping-pro-woocommerce/" target="_blank">Flexible Shipping for WooCommerce</a>.
 * Version: 3.6.6
 * Author: WP Desk
 * Author URI: https://www.wpdesk.net/
 * Text Domain: woocommerce_activepayments
 * Domain Path: /lang/
 * Requires at least: 4.5
 * Tested up to: 5.6
 * WC requires at least: 3.7.0
 * WC tested up to: 5.0
 * Requires PHP: 5.6
 *
 * Copyright 2017 WP Desk Ltd.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* THIS VARIABLE CAN BE CHANGED AUTOMATICALLY */
$plugin_version = '3.6.6';

$plugin_name        = 'WooCommerce Active Payments';
$plugin_class_name  = 'WPDesk_Active_Payments_Plugin';
$plugin_text_domain = 'woocommerce_activepayments';
$product_id         = 'WooCommerce Active Payments';
$plugin_file        = __FILE__;
$plugin_dir         = dirname( __FILE__ );

$requirements = array(
	'php'     => '5.6',
	'wp'      => '4.5',
	'plugins' => array(
		array(
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
		),
	),
);

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow/src/plugin-init-php52.php';
