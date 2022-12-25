<?php
/**
 * File that define P24_Core class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Core methods for Przelewy 24 plugin.
 */
class P24_Core {

	/**
	 * String to add to loaded scripts.
	 *
	 * @var string
	 */
	const SCRIPTS_VERSION = '1.6.0';

	/**
	 * The internal version to use.
	 *
	 * Due to technical reasons, there may be few other places with the same purpose.
	 * This one should take precedence.
	 *
	 * @var string
	 */
	const INTERNAL_VERSION = '1.0.6';

	/**
	 * Key used to mark when Przelewy24 has been used to pay for order.
	 *
	 * @var string
	 */
	const CHOSEN_TIMESTAMP_META_KEY = '_p24_chosen_timestamp';

	/**
	 * Key to store Przelewy24 payment method id.
	 */
	const P24_METHOD_META_KEY = 'p24_method';

	/**
	 * Key used to store session id, which may be used later i.e. for refunds
	 */
	const ORDER_SESSION_ID_KEY = '_p24_order_session_id';

	/**
	 * Key used to store Przelewy24 internal order id.
	 */
	const ORDER_P24_ID = '_p24_order_id';

	/**
	 * The null or P24_Multi_Currency instance.
	 *
	 * @var null|P24_Multi_Currency
	 */
	private $multi_currency = null;

	/**
	 * The P24_Request_Support instance.
	 *
	 * @var P24_Request_Support
	 */
	private $request_support;

	/**
	 * The P24_Config_Menu instance.
	 *
	 * @var P24_Config_Menu;
	 */
	private $config_menu;

	/**
	 * The instance of class configuring WP menu.
	 *
	 * @var P24_Multi_Currency_Menu
	 */
	private $wp_menu_support;

	/**
	 * The WC_Gateway_Przelewy24 instance.
	 *
	 * @var WC_Gateway_Przelewy24
	 */
	private $gateway;

	/**
	 * Context provider.
	 *
	 * @var P24_Context_Provider
	 */
	private $context_provider;

	/**
	 * HTML for SOAP BLIK.
	 *
	 * Most logic is in the constructor.
	 *
	 * @var P24_Blik_Html
	 */
	private $soap_blik_html;

	/**
	 * Status provider.
	 *
	 * @var P24_Status_Provider
	 */
	private $status_provider;

	/**
	 * Status_decorator.
	 *
	 * @var P24_Status_Decorator
	 */
	private $status_decorator;

	/**
	 * Support for extra gateway.
	 *
	 * @var P24_Extra_Gateway_Support
	 */
	private $eg_support;

	/**
	 * External_multicurrency.
	 *
	 * @var P24_MC_Pool
	 */
	private $mc_pool;

	/**
	 * Subscription class.
	 *
	 * @var P24_Subscription
	 */
	private $subscription;

	/**
	 * Icon generator.
	 *
	 * @var P24_Icon_Svg_Generator
	 */
	private $icon_generator;

	/**
	 * Configuration helper
	 *
	 * @var P24_Config_Eraser
	 */
	private $config_eraser;

	/**
	 * Construct class instance.
	 */
	public function __construct() {
		$this->request_support  = new P24_Request_Support();
		$this->context_provider = new P24_Context_Provider();
		$this->wp_menu_support  = new P24_Multi_Currency_Menu( $this );
		$config_factory         = array( &$this->gateway, 'load_settings_from_db_formatted' );
		$this->status_provider  = new P24_Status_Provider();
		$this->status_decorator = new P24_Status_Decorator( $config_factory, $this->status_provider, $this );
		$this->eg_support       = new P24_Extra_Gateway_Support( $this );
		$this->mc_pool          = new P24_MC_Pool( $this );
		$this->subscription     = new P24_Subscription( $this );
		$this->config_eraser    = new P24_Config_Eraser( $this );
		if ( ! $this->is_in_user_mode() ) {
			$this->config_menu = new P24_Config_Menu( $this );
		}
	}

	/**
	 * Check if page is in user mode.
	 *
	 * @return bool
	 */
	public function is_in_user_mode() {
		if ( is_admin() ) {
			return false;
		} elseif ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return false;
		} elseif ( $this->is_in_json_mode() ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check if application is in JSON mode.
	 *
	 * @return bool
	 */
	public function is_in_json_mode() {
		$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI' );
		return (bool) preg_match( '/^\\/wp\\-json\\//', $request_uri );
	}

	/**
	 * Check if internal multi currency is activated.
	 *
	 * @return bool
	 */
	public function is_internal_multi_currency_active() {
		return (bool) $this->multi_currency;
	}

	/**
	 * Get any active multi currency instance or proxy.
	 *
	 * @return P24_MC_Interface
	 */
	public function get_any_active_mc() {
		return $this->mc_pool->get_preffered_mc();
	}

	/**
	 * Get_external_active_mc.
	 *
	 * @return null|P24_MC_Interface
	 */
	public function get_external_active_mc() {
		return $this->mc_pool->get_external_mc();
	}

	/**
	 * Return multi currency instance.
	 *
	 * Should be called only if multi currency i active.
	 *
	 * @return P24_Multi_Currency
	 * @throws LogicException If there is no instance.
	 */
	public function get_multi_currency_instance() {
		if ( $this->is_internal_multi_currency_active() ) {
			return $this->multi_currency;
		} else {
			throw new LogicException( 'Multi currency is not active. It should be tested.' );
		}
	}

	/**
	 * Get status provider instance.
	 *
	 * @return P24_Status_Provider
	 */
	public function get_status_provider_instance() {
		return $this->status_provider;
	}

	/**
	 * Get status decorator instance.
	 *
	 * @return P24_Status_Decorator
	 */
	public function get_status_decorator_instance() {
		return $this->status_decorator;
	}

	/**
	 * Try override active currency.
	 *
	 * The communication with Przelewy24 is quite late.
	 *
	 * @param P24_Communication_Parser $parser The P24_Communication_Parser instance.
	 */
	public function try_override_active_currency( P24_Communication_Parser $parser ) {
		if ( $this->is_internal_multi_currency_active() ) {
			$this->multi_currency->try_override_active_currency( $parser );
		}
	}

	/**
	 * Return current instance i parameter is null.
	 *
	 * This should be useful in filters.
	 *
	 * @param mixed $default Default value from filter.
	 * @return P24_Core
	 */
	public function get_this_if_null( $default ) {
		return $default ? $default : $this;
	}

	/**
	 * Render template and output.
	 *
	 * @param string $template The name of template.
	 * @param array  $params The array of parameters.
	 * @throws LogicException If the file is not found.
	 */
	public function render_template( $template, $params = array() ) {
		$dir  = __DIR__ . '/../templates/';
		$file = $template . '.php';
		wc_get_template( $file, $params, $dir, $dir );
	}

	/**
	 * Check if multi currency should be activated.
	 *
	 * @return bool
	 */
	public function should_activate_multi_currency() {
		$common = get_option( P24_Request_Support::OPTION_KEY_COMMON, array() );

		return array_key_exists( 'p24_multi_currency', $common ) && 'yes' === $common['p24_multi_currency'];
	}

	/**
	 * Check if order created notification should be activated.
	 *
	 * @return bool
	 */
	public function should_activate_order_created_notification() {
		$common = get_option( P24_Request_Support::OPTION_KEY_COMMON, array() );

		return array_key_exists( 'p24_notification_order_created', $common ) && 'yes' === $common['p24_notification_order_created'];
	}

	/**
	 * Register gateway.
	 *
	 * The constructor of gateway has to be called in external plugin.
	 * This function may be called before the main gateway is in a usable state.
	 *
	 * @param WC_Gateway_Przelewy24 $gateway The gateway instance.
	 */
	public function register_gateway( WC_Gateway_Przelewy24 $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * Code to execute after main gateway initiation.
	 *
	 * This function should be called on the main gateway becoming usable.
	 *
	 * @throws LogicException Throws if executed too early.
	 */
	public function after_main_gateway_initiation() {
		if ( ! isset( $this->gateway ) ) {
			throw new LogicException( 'This function has to be called after register_gateway.' );
		}

		$this->eg_support->prep_extra_gateways( $this->gateway );
		$this->soap_blik_html = new P24_Blik_Html( $this );
	}

	/**
	 * Get config for currency.
	 *
	 * @param null|string $currency The currency for requested config.
	 * @return P24_Config_Accessor
	 * @throws LogicException If there is no gateway created.
	 */
	public function get_config_for_currency( $currency = null ) {
		if ( ! $this->gateway ) {
			throw new LogicException( 'Gateway in not registered yet.' );
		}
		return $this->gateway->load_settings_from_db_formatted( $currency );
	}

	/**
	 * Get P24_Message_Validator instance.
	 *
	 * @return P24_Message_Validator
	 */
	public function get_message_validator() {
		return new P24_Message_Validator();
	}

	/**
	 * Get P24_Communication_Parser instance.
	 *
	 * @return P24_Communication_Parser
	 */
	public function get_communication_parser() {
		$message_validator = $this->get_message_validator();
		return new P24_Communication_Parser( $message_validator );
	}

	/**
	 * Get default currency.
	 *
	 * @return string
	 */
	public function get_default_currency() {
		if ( $this->is_internal_multi_currency_active() ) {
			return $this->multi_currency->get_default_currency();
		} else {
			return get_woocommerce_currency();
		}
	}

	/**
	 * Get all currencies including default.
	 *
	 * @return array
	 */
	public function get_available_currencies_or_default() {
		$mc = $this->mc_pool->get_preffered_mc();
		if ( $mc->is_multi_currency_active() ) {
			$currencies = $mc->get_available_currencies();
		} else {
			$currencies = array( $this->get_default_currency() );
		}

		return $currencies;
	}

	/**
	 * Late configuration after Woocommerce init.
	 */
	public function after_woocommerce_init() {
		$this->request_support->analyse();
		$this->request_support->flush_options();
		$this->status_provider->try_add_new( $this->request_support->get_order_status_changes() );
		$this->status_decorator->bind_events();
		$currency_changes = $this->request_support->get_currency_changes();
		if ( $this->should_activate_multi_currency() ) {
			/* The logic to set active currency is in P24_Multi_Currency class. */
			$this->multi_currency = new P24_Multi_Currency( $this, $currency_changes );
			$this->multi_currency->bind_events();
			if ( is_admin() ) {
				$this->config_menu->bind_multi_currency_events();
			}
		}
		if ( $currency_changes ) {
			$this->mc_pool->set_active_currency( $currency_changes );
		}
	}

	/**
	 * Check if order is cancellable.
	 *
	 * @param bool     $default Default value.
	 * @param WC_Order $order The order to check.
	 *
	 * @return mixed
	 */
	public function check_if_cancellable( $default, $order ) {
		if ( $default ) {
			return $default;
		} elseif ( ! $order instanceof WC_Order ) {
			return $default;
		} elseif ( WC_Gateway_Przelewy24::PAYMENT_METHOD === $order->get_payment_method() ) {
			$minutes   = (int) get_option( P24_Woo_Commerce_Internals::HOLD_STOCK_MINUTES );
			$timestamp = $order->get_meta( self::CHOSEN_TIMESTAMP_META_KEY );
			if ( $minutes && $timestamp ) {
				$now     = time();
				$seconds = $minutes * 60;

				return ( $timestamp + $seconds ) < $now;
			}
		}

		return $default;
	}

	/**
	 * Check need for extra statuses.
	 *
	 * @return bool
	 */
	public function check_need_for_extra_statuses() {
		if ( $this->is_internal_multi_currency_active() ) {
			$active = $this->multi_currency->get_available_currencies();
			foreach ( $active as $one ) {
				$config = P24_Settings_Helper::load_settings( $one );
				if ( ! $config ) {
					continue;
				}
				$config->access_mode_to_strict();
				if ( $config->get_p24_use_special_status() ) {
					return true;
				}
			}
		} else {
			$currency = $this->get_default_currency();
			$config   = P24_Settings_Helper::load_settings( $currency );
			if ( ! $config ) {
				return false;
			}
			$config->access_mode_to_strict();

			return $config->get_p24_use_special_status();
		}

		return false;
	}

	/**
	 * Add scripts used on admin page.
	 */
	public function add_admin_scripts() {
		wp_enqueue_style( 'p24_admin', PRZELEWY24_URI . 'assets/css/p24_style_admin.css', array(), self::SCRIPTS_VERSION );
	}

	/**
	 * Get cached icon generator.
	 *
	 * @return P24_Icon_Svg_Generator
	 */
	public function get_cached_icon_generator() {
		if ( ! isset( $this->icon_generator ) ) {
			$config               = $this->gateway->load_settings_from_db_formatted();
			$this->icon_generator = new P24_Icon_Svg_Generator( $config );
		}

		return $this->icon_generator;
	}

	/**
	 * Generate hidden icon url list.
	 */
	public function generate_hidden_icon_url_list() {
		global $current_section;
		if ( 'przelewy24' !== $current_section ) {
			/* Nothing to add. */
			return;
		}

		$icon_generator = $this->get_cached_icon_generator();
		$icons          = $icon_generator->get_all();

		echo '<table id="p24-hidden-bank-icon-list" style="display: none;">';
		foreach ( $icons as $id => $icon ) {
			echo '<tr class="js-bank-id-', esc_attr( $id ), '">';
			echo '<td class="js-icon-type-base">', esc_html( $icon['base'] ), '</td>';
			echo '<td class="js-icon-type-mobile">', esc_html( $icon['mobile'] ), '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}

	/**
	 * Bind events.
	 */
	public function bind_core_events() {
		add_filter( 'przelewy24_plugin_instance', array( $this, 'get_this_if_null' ) );
		add_action( 'woocommerce_init', array( $this, 'after_woocommerce_init' ) );
		add_filter( 'woocommerce_cancel_unpaid_order', array( $this, 'check_if_cancellable' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
		add_filter( 'woocommerce_settings_checkout', array( $this, 'generate_hidden_icon_url_list' ) );
		$this->wp_menu_support->bind_events();
		$this->eg_support->bind_common_events();
		$this->mc_pool->bind_events();
		$this->subscription->bind_core_events();
		$this->config_eraser->bind_events();
		if ( is_admin() ) {
			$this->config_menu->bind_common_events();
		}

        add_action( 'rest_api_init', function () {
            register_rest_route( 'przelewy24/v1', '/create-payment', array(
                'methods' => 'POST',
                'callback' => array($this, 'przelewy24_request'),
                'permission_callback' => '__return_true',
            ) );
        } );

    }

    public function przelewy24_request($data) {
        return $this->gateway->przelewy24_request($data);
    }


	/**
	 * Get context provider.
	 */
	public function get_context_provider() {
		return $this->context_provider;
	}

	/**
	 * Get P24_Blik_Html.
	 *
	 * @return P24_Blik_Html
	 */
	public function get_soap_blik_html() {
		return $this->soap_blik_html;
	}

	/**
	 * Converts money amount from stringified float (1.10) to cents in int.
	 *
	 * @param string $string Stringified float.
	 *
	 * @return int
	 */
	public static function convert_stringified_float_to_cents( $string ) {
		list( $fulls, $cents ) = explode( '.', $string );

		$sum = (int) $fulls * 100;

		if ( $cents ) {
			$sum += $cents;
		}

		return $sum;
	}
}
