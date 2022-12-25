<?php
/**
 * Plugin Name: WooCommerce PayU
 * Plugin URI: https://www.wpdesk.pl/sklep/payu-woocommerce/
 * Description: Wtyczka do WooCommerce. Bramka płatności dla systemu PayU.
 * Version: 5.0.7
 * Author: WP Desk
 * Text Domain: woocommerce_payu
 * Domain Path: /languages/
 * Author URI: https://www.wpdesk.pl/
 * Requires at least: 5.2
 * Tested up to: 5.8
 * WC requires at least: 5.8
 * WC tested up to: 6.1
 * Domain Path: /lang/
 * Requires PHP: 7.0
 *
 * Copyright 2016 WP Desk Ltd.
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

/* THESE TWO VARIABLES CAN BE CHANGED AUTOMATICALLY */

$plugin_version           = '5.0.7';
$plugin_release_timestamp = '2021-10-28 15:35';

$plugin_name        = 'WooCommerce PayU';
$product_id         = 'WooCommerce PayU';
$plugin_class_name  = 'WC_Gateway_Payu_Plugin';
$plugin_text_domain = 'woocommerce_payu';
$plugin_file        = __FILE__;
$plugin_dir         = dirname( __FILE__ );

$plugin_shops = [
	'pl_PL'   => 'https://www.wpdesk.pl/',
	'default' => 'https://www.wpdesk.pl/',
];

$requirements = array(
	'php'     => '7.0',
	'wp'      => '4.5',
	'plugins' => array(
		array(
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
		),
	),
);

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow/src/plugin-init-php52.php';
