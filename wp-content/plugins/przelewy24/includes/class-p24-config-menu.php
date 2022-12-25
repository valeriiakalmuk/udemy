<?php
/**
 * File that define P24_Config class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Methods for Przelewy 24 plugin to display admin config.
 *
 * Processing of config is in different class.
 */
class P24_Config_Menu {

	/**
	 * Instance of core of plugin.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * Construct class instance.
	 *
	 * @param P24_Core $plugin_core The core class for plugin.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
	}

	/**
	 * Render multi currency config page.
	 */
	public function render_mc_config_page() {
		$tab           = empty( $_GET['tab'] ) ? 'main' : sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$multicurrency = $this->plugin_core->is_internal_multi_currency_active();
		switch ( $tab ) {
			case 'main':
				$this->render_config_tabs( $tab, $multicurrency );
				$this->render_config_mc_main_page();
				break;
			case 'multipliers':
				$this->render_config_tabs( $tab, $multicurrency );
				$this->render_config_multipliers_page();
				break;
			case 'formats':
				$this->render_config_tabs( $tab, $multicurrency );
				$this->render_config_format_page();
				break;
		}
	}

	/**
	 * Render tabs of config page.
	 *
	 * @param string $tab The active tab.
	 * @param bool   $multicurrency If multi currency is active.
	 */
	private function render_config_tabs( $tab, $multicurrency ) {
		$params = compact( 'tab', 'multicurrency' );
		$this->plugin_core->render_template( 'multi-currency-tabs', $params );
	}

	/**
	 * Render form to activate multi currency module.
	 *
	 * @throws LogicException Not expected.
	 */
	private function render_config_mc_main_page() {
		$multi_currency_instance = $this->plugin_core->get_any_active_mc();
		if ( $multi_currency_instance->is_internal() ) {
			if ( ! $multi_currency_instance instanceof P24_Multi_Currency ) {
				$class = get_class( $multi_currency_instance );
				throw new LogicException( "The implementation of $class has broken is_internal method or the logic of application has changed." );
			}
			$this->render_config_main_page_extended( $multi_currency_instance );

			return;
		}
		$value                            = $this->plugin_core->should_activate_multi_currency();
		$order_created_notification_value = $this->plugin_core->should_activate_order_created_notification();
		$params                           = compact( 'multi_currency_instance', 'value', 'order_created_notification_value' );
		$this->plugin_core->render_template( 'multi-currency-main', $params );
	}

	/**
	 * Render config main page extended by reports currency field.
	 *
	 * @param P24_Multi_Currency $multi_currency_instance Multi currency instance.
	 */
	private function render_config_main_page_extended( P24_Multi_Currency $multi_currency_instance ) {
		$value            = $this->plugin_core->should_activate_multi_currency();
		$currency_options = $multi_currency_instance->get_available_currencies();
		$report_currency  = P24_Multi_Currency::get_admin_reports_currency( get_woocommerce_currency() );

		wp_verify_nonce( null ); /* There is no nonce in request. */
		if ( isset( $_POST['p24_reports_currency'] ) ) {
			$report_currency = sanitize_text_field( wp_unslash( $_POST['p24_reports_currency'] ) );
		}

		$order_created_notification_value = $this->plugin_core->should_activate_order_created_notification();
		$params                           = compact( 'multi_currency_instance', 'value', 'currency_options', 'report_currency', 'order_created_notification_value' );
		$this->plugin_core->render_template( 'multi-currency-main', $params );
	}

	/**
	 * Render form to set currency multipliers.
	 */
	private function render_config_multipliers_page() {
		$available                     = get_woocommerce_currencies();
		$multipliers                   = $this->plugin_core->get_multi_currency_instance()->get_multipliers();
		$base_currency                 = P24_Woo_Commerce_Low_Level_Getter::get_unhooked_currency_form_woocommerce();
		$multipliers[ $base_currency ] = 1;
		$params                        = compact( 'multipliers', 'base_currency', 'available' );
		$this->plugin_core->render_template( 'multi-currency-multipliers', $params );
	}

	/**
	 * Render form to change format of currency.
	 */
	private function render_config_format_page() {
		$formats         = get_option( 'przelewy24_multi_currency_formats', array() );
		$active_currency = $this->plugin_core->get_multi_currency_instance()->get_active_currency();
		if ( array_key_exists( $active_currency, $formats ) ) {
			$format = $formats[ $active_currency ];
		} else {
			$base_currency = P24_Woo_Commerce_Low_Level_Getter::get_unhooked_currency_form_woocommerce();
			if ( array_key_exists( $base_currency, $formats ) ) {
				$format = $formats[ $base_currency ];
			} else {
				$format = array(
					'currency_pos'       => get_option( 'woocommerce_currency_pos' ),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'decimals'           => wc_get_price_decimals(),
				);
			}
		}
		$currency_options = get_przelewy24_multi_currency_options();

		$params = compact( 'format', 'active_currency', 'currency_options' );
		$this->plugin_core->render_template( 'multi-currency-formats', $params );
	}

	/**
	 * Render status page.
	 */
	public function render_order_status_page() {
		$tab = empty( $_GET['tab'] ) ? 'main' : sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		switch ( $tab ) {
			case 'main':
				$this->render_order_status_tabs( $tab );
				$this->render_order_status_activation_page();
				break;
			case 'list':
				$this->render_order_status_tabs( $tab );
				$this->render_order_status_config_page();
				break;
		}
	}

	/**
	 * Render tabs for statuses.
	 *
	 * @param string $tab The active tab.
	 */
	private function render_order_status_tabs( $tab ) {
		$is_active = P24_Status_Decorator::is_active();
		$params    = compact( 'tab', 'is_active' );
		$this->plugin_core->render_template( 'statuses-tabs', $params );
	}

	/**
	 * Render order status page.
	 */
	public function render_order_status_activation_page() {
		$is_active = P24_Status_Decorator::is_active();
		$params    = compact( 'is_active' );
		$this->plugin_core->render_template( 'status-config', $params );
	}

	/**
	 * Render order status config page.
	 */
	public function render_order_status_config_page() {
		$status_provider = $this->plugin_core->get_status_provider_instance();

		$statuses  = P24_Status_Provider::get_formatted_config();
		$error     = $status_provider->get_adding_error();
		$new_code  = $status_provider->get_proposed_code_if_error();
		$new_label = $status_provider->get_proposed_label_if_error();
		$params    = compact( 'statuses', 'error', 'new_code', 'new_label' );
		$this->plugin_core->render_template( 'statuses', $params );
	}

	/**
	 * Render subscription page.
	 */
	public function render_subscription_page() {
		$tab = empty( $_GET['tab'] ) ? 'main' : sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		switch ( $tab ) {
			case 'main':
				$this->render_subscription_tabs( $tab );
				$this->render_subscription_config_page();
				break;
			case 'list':
				$this->render_subscription_tabs( $tab );
				$this->render_subscription_list();
				break;
			case 'inactive':
				$this->render_subscription_tabs( $tab );
				$this->render_inactive_subscription_list();
				break;
		}
	}

	/**
	 * Render tabs for subscription.
	 *
	 * @param string $tab The active tab.
	 */
	private function render_subscription_tabs( $tab ) {
		$is_active = P24_Subscription_Config::is_active();
		$params    = compact( 'tab', 'is_active' );
		$this->plugin_core->render_template( 'subscriptions-tabs', $params );
	}

	/**
	 * Render subscription config page.
	 */
	public function render_subscription_config_page() {
		$params = array(
			'is_active'     => P24_Subscription_Config::is_active(),
			'days_to_renew' => P24_Subscription_Config::days_to_renew(),
			'page_id'       => P24_Subscription_Config::page_id(),
		);
		$this->plugin_core->render_template( 'subscriptions-config', $params );
	}

	/**
	 * Render subscription list.
	 */
	public function render_subscription_list() {
		P24_Subscription_Admin::parse_cancellation_request();
		$params = array(
			'list' => P24_Subscription_Db::get_active_list(),
		);
		$this->plugin_core->render_template( 'subscriptions-list', $params );
	}

	/**
	 * Render inactive subscription list.
	 */
	public function render_inactive_subscription_list() {
		$params = array(
			'inactive_list' => P24_Subscription_Db::get_inactive_list(),
		);
		$this->plugin_core->render_template( 'subscriptions-list-inactive', $params );
	}

	/**
	 * Prepare common config menus.
	 */
	public function prepare_config_menu() {
		add_submenu_page(
			'woocommerce',
			'P24 Multi Currency',
			'P24 Multi Currency',
			'manage_options',
			'p24-multi-currency',
			array( $this, 'render_mc_config_page' )
		);
		if ( $this->plugin_core->check_need_for_extra_statuses() ) {
			add_submenu_page(
				'woocommerce',
				'P24 Order Status',
				'P24 Order Status',
				'manage_options',
				'p24-order-status',
				array( $this, 'render_order_status_page' )
			);
		}
		add_submenu_page(
			'woocommerce',
			'P24 Subscriptions',
			'P24 Subscriptions',
			'manage_options',
			'p24-subscription',
			array( $this, 'render_subscription_page' )
		);
	}

	/**
	 * Add scripts used on admin page.
	 */
	public function add_admin_scripts() {
		wp_enqueue_style( 'p24_multi_currency_admin', PRZELEWY24_URI . 'assets/css/p24_multi_currency_style_admin.css', array(), P24_Core::SCRIPTS_VERSION );
	}

	/**
	 * Update WooCommerce settings panels.
	 *
	 * The MultiCurrency make few changes.
	 * Few config items have to be renamed or overwritten.
	 * The required config is added by different functions.
	 *
	 * @param array $input The WooCommerce settings.
	 * @return array
	 */
	public function clear_woocommerce_settings( $input ) {
		$ret = array();
		foreach ( $input as $k => $v ) {
			switch ( $v['id'] ) {
				case P24_Woo_Commerce_Internals::CURRENCY:
					/* Change label. */
					$v['title'] = __( 'Waluta podstawowa', 'przelewy24' );
					$v['desc']  = null;
					$ret[ $k ]  = $v;
					break;
				case P24_Woo_Commerce_Internals::CURRENCY_POS:
				case P24_Woo_Commerce_Internals::PRICE_THOUSAND_SEP:
				case P24_Woo_Commerce_Internals::PRICE_DECIMAL_SEP:
				case P24_Woo_Commerce_Internals::PRICE_NUM_DECIMALS:
					/* These options are overwritten by multi currency. */
					break;
				default:
					$ret[ $k ] = $v;
			}
		}
		return $ret;
	}

	/**
	 * Add box to set currency for order.
	 *
	 * This box is used on admin panel.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function add_admin_order_change_currency( WP_Post $post ) {
		$currency_options = $this->plugin_core->get_multi_currency_instance()->get_available_currencies();
		$params           = compact( 'post', 'currency_options' );
		$this->plugin_core->render_template( 'multi-currency-order-edit', $params );
	}

	/**
	 * Add all meta boxes for different pages.
	 */
	public function add_meta_boxes() {
		foreach ( wc_get_order_types( 'order-meta-boxes' ) as $type ) {
			add_meta_box( 'p24_admin_order_multi_currency', __( 'Aktywna waluta', 'przelewy24' ), array( $this, 'add_admin_order_change_currency' ), $type, 'side', 'high' );
		}
	}

	/**
	 * Bind common events.
	 */
	public function bind_common_events() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'prepare_config_menu' ) );
	}

	/**
	 * Bind multi currency events.
	 */
	public function bind_multi_currency_events() {
		add_filter( 'woocommerce_general_settings', array( $this, 'clear_woocommerce_settings' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

}
