<?php
/**
 * Plugin Name: WC Pickup Store
 * Plugin URI: https://www.keylormendoza.com/plugins/wc-pickup-store/
 * Description: Allows you to set up a custom post type for stores available to use it as shipping method Local pickup in WooCommerce. It also allows your clients to choose an store on the Checkout page and also adds the store fields to the order details and email.
 * Version: 1.8.3
 * Requires at least: 4.7
 * Tested up to: 5.9.2
 * WC requires at least: 3.0
 * WC tested up to: 6.3.1
 * Author: Keylor Mendoza A.
 * Author URI: https://www.keylormendoza.com
 * License: GPLv2
 * Text Domain: wc-pickup-store
 */

if ( !defined( 'ABSPATH' ) ) { exit; }

if ( !defined( 'WPS_PLUGIN_FILE' ) ) {
	define( 'WPS_PLUGIN_FILE', plugin_basename( __FILE__ ) );
}

if ( !defined( 'WPS_PLUGIN_VERSION' ) ) {
	define( 'WPS_PLUGIN_VERSION', '1.8.3' );
}

if ( !defined( 'WPS_PLUGIN_PATH' ) ) {
	define( 'WPS_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( !defined( 'WPS_PLUGIN_DIR_URL' ) ) {
	define( 'WPS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Admin Notices
 * 
 * @since 1.0.0
 * @version 1.8.2
 */
if ( ! in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
	add_action('admin_notices', 'wps_store_check_init_notice');
	return;
}

function wps_store_check_init_notice() {
	// Check if WooCommerce is active
	if ( current_user_can( 'activate_plugins') ) {
		?>
		<div id="message" class="error">
			<p>
				<?php
				printf(
					__('%1$s requires %2$sWooCommerce%3$s to be active.', 'wc-pickup-store'),
					'<strong>WC Pickup Store</strong>',
					'<a href="http://wordpress.org/plugins/woocommerce/" target="_blank" >',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
		return;
	}
}

/**
 * Plugin files
 */
include WPS_PLUGIN_PATH . '/includes/class-wps-init.php';
include WPS_PLUGIN_PATH . '/includes/wps-functions.php';
include WPS_PLUGIN_PATH . '/includes/cpt-store.php';
include WPS_PLUGIN_PATH . '/includes/admin/wps-admin.php';

include WPS_PLUGIN_PATH . '/includes/integrations/class-vc_stores.php';
include WPS_PLUGIN_PATH . '/includes/integrations/class-widget-stores.php';

/**
 * Notice stores without country
 * 
 * @since 1.5.24
 * @version 1.8.3
 */
function wps_store_country_notice() {
	// Update stores Country
	if ( version_compare( WPS_PLUGIN_VERSION, '1.5.24' ) >= 0 ) {
		if ( !get_option( 'wps_countries_updated' ) ) {
			?>
			<div id="message" class="notice notice-error">
				<p><?php
					$id = "wc_pickup_store";
					$update_url = sprintf(admin_url('admin.php?page=wc-settings&tab=shipping&section=%s&update_country=1'), $id);
					printf(
						__('Since version %1$s, a new Country validation was added to %2$s. Please, update stores without country manually or use the default country %3$s %4$shere%5$s.', 'wc-pickup-store'),
						'<strong>1.5.24</strong>',
						'<strong>WC Pickup Store</strong>',
						'<strong>' . wps_get_wc_default_country() . '</strong>',
						'<a href="' . $update_url . '" >',
						'</a>'
					);
					?></p>
			</div>
			<?php
		}
	}
}
add_action( 'admin_notices', 'wps_store_country_notice' );
