<?php
/**
 * File that define P24_Subscription class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class P24_Subscription
 */
class P24_Subscription {

	/**
	 * The P24_Core instance.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * The P24_Core instance.
	 *
	 * @var P24_Subscription_User
	 */
	private $for_user;

	/**
	 * The P24_Core instance.
	 *
	 * @var P24_Subscription_Admin
	 */
	private $for_admin;

	/**
	 * The P24_Subscription_Api instance.
	 *
	 * @var P24_Subscription_Api
	 */
	private $api;

	/**
	 * The constructor.
	 *
	 * @param P24_Core $plugin_core The P24_Core instance.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
		$this->for_user    = new P24_Subscription_User( $plugin_core );
		$this->for_admin   = new P24_Subscription_Admin( $plugin_core );
		$this->api         = new P24_Subscription_Api();
	}

	/**
	 * Register subcription product type
	 *
	 * @param array $types Provided types.
	 * @return array Extended types.
	 */
	public function register_product_type( $types ) {
		$types[ P24_No_Mc_Product_Subscription::TYPE ] = __( 'Subskrypcja P24' );

		return $types;
	}

	/**
	 * Detect subcription product class.
	 *
	 * @param string $classname Default classname.
	 * @param string $product_type Product type.
	 * @return string
	 */
	public function subscription_class_detector( $classname, $product_type ) {
		if ( P24_No_Mc_Product_Subscription::TYPE === $product_type ) {
			$classname = P24_No_Mc_Product_Subscription::class;
		}

		return $classname;
	}

	/**
	 * Render subscription panel.
	 */
	public function render_subscription_panel() {
		global $post;
		$post_id = (int) $post->ID;
		$data    = array(
			'files' => self::files_for_subscription( $post_id ),
		);
		$this->plugin_core->render_template( 'subscriptions-product', $data );
	}

	/**
	 * Save subscription fields.
	 *
	 * @param int     $post_id Id of post.
	 * @param WP_Post $post The post itself.
	 */
	public function save_subscription_fields( $post_id, $post ) {
		if ( isset( $_POST['_wpnonce'] ) ) {
			wp_verify_nonce( sanitize_textarea_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-post_' . $post->ID );
		}

		if ( isset( $_POST['product-type'] ) ) {
			$product_type = sanitize_text_field( wp_unslash( $_POST['product-type'] ) );
			if ( P24_No_Mc_Product_Subscription::TYPE !== $product_type ) {
				/* Different product type, nothing sane to do. */
				return;
			}
		}

		if ( isset( $_POST['_subscription_price'] ) ) {
			/* Update two metas with the same value. For better compatibility. */
			$price = sanitize_text_field( wp_unslash( $_POST['_subscription_price'] ) );
			update_post_meta( $post_id, '_subscription_price', $price );
			update_post_meta( $post_id, '_price', $price );
		}

		/* The WordPress standards allow only the simplest for lops. */
		$i             = 0;
		$files_to_save = array();
		while ( array_key_exists( $i, $_POST['_p24_sub_file_urls'] ) ) {
			$url  = sanitize_text_field( wp_unslash( $_POST['_p24_sub_file_urls'][ $i ] ) );
			$name = 'file_' . $i;
			if ( array_key_exists( $i, $_POST['_p24_sub_file_names'] ) ) {
				$provided_name = sanitize_text_field( wp_unslash( $_POST['_p24_sub_file_names'][ $i ] ) );
				if ( '' !== $provided_name ) {
					$name = $provided_name;
				}
			}
			$files_to_save[] = compact( 'name', 'url' );
			$i++;
		}

		update_post_meta( $post_id, 'p24_sub_files', $files_to_save );

		if ( isset( $_POST['_days'] ) ) {
			$days = (int) sanitize_text_field( wp_unslash( $_POST['_days'] ) );
			if ( $days < 1 ) {
				$days = 1;
			}
			update_post_meta( $post_id, '_days', $days );
		}
	}

	/**
	 * Output the simple product add to cart area.
	 */
	public function render_subscription_buy_form() {
		$nonce_action = 'p24_add_subscription';
		if ( isset( $_POST['_wpnonce'] ) ) {
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), $nonce_action );
			$post_data = $_POST;
		} else {
			$post_data = array();
		}
		$data = compact( 'post_data', 'nonce_action' );
		$this->plugin_core->render_template( 'add-subscription-to-cart', $data );
	}

	/**
	 * Check for subscriptions.
	 *
	 * This code should execute only if order is paid and saved.
	 *
	 * @param int $order_id The id of existing order.
	 * @throws LogicException If the function is misused.
	 */
	public function check_for_subscriptions_in_order( $order_id ) {
		$order_id = (int) $order_id;
		if ( ! $order_id ) {
			throw new LogicException( 'Order should be present before call to this function.' );
		}
		$order = new WC_Order( $order_id );
		if ( ! $order->is_paid() ) {
			throw new LogicException( 'Order should be paid before call to this function.' );
		}
		$user = $order->get_user();
		if ( ! $user ) {
			/* If there is no user, the subscription cannot be created. */
			return;
		}
		$items = $order->get_items();

		foreach ( $items as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}
			$subscription = $item->get_product();
			if ( $subscription instanceof P24_No_Mc_Product_Subscription ) {
				$quantity = (int) $item->get_quantity();
				P24_Subscription_Db::extend_subscription( $subscription, $quantity, $user, $order );
			}
		}
	}

	/**
	 * Add_card_ref.
	 *
	 * @param WC_Order    $order Order.
	 * @param string|null $cart_ref Kay of card, it is not reference id for Przelewy24.
	 */
	public function add_card_ref( $order, $cart_ref ) {
		if ( $cart_ref ) {
			P24_Subscription_Db::add_card_reference( $order, $cart_ref );
		}
	}

	/**
	 * Charge_card.
	 *
	 * @param WC_Order $order Order to pay.
	 * @param string   $card_ref Kay of card, it is not reference id for Przelewy24.
	 * @return bool
	 */
	private function charge_card( $order, $card_ref ) {
		$data = P24_Sub_Generator::generate_payload_for_rest( $order );
		if ( ! $data ) {
			return false;
		}

		$card = P24_Sub_Generator::find_saved_card( $card_ref, $order->get_user_id() );
		if ( ! $card ) {
			return false;
		}

		$config_accessor          = P24_Settings_Helper::load_settings( $order->get_currency() );
		$data['regulationAccept'] = true;
		$rest_transaction         = new P24_Rest_Transaction( $config_accessor );
		$data['methodRefId']      = $card['ref'];

		$transaction_info = $rest_transaction->register( $data );
		$token            = $transaction_info['data']['token'];

		if ( ! $token ) {
			return false;
		}

		$rest_card    = new P24_Rest_Card( $config_accessor );
		$payment_info = $rest_card->chargeWithout3ds( $token );
		if ( isset( $payment_info['data']['orderId'] ) ) {
			$order_id = $payment_info['data']['orderId'];
			$order->update_meta_data( P24_Core::ORDER_P24_ID, $order_id );
			$order->save_meta_data();
		}

		return true;

	}

	/**
	 * Create subscription.
	 *
	 * @param int    $product_id Product id.
	 * @param int    $user_id User id.
	 * @param int    $last_order_id Last order id.
	 * @param string $card_ref Kay of card, it is not reference id for Przelewy24.
	 *
	 * @throws LogicException This error is not expected.
	 */
	private function create_subscription( $product_id, $user_id, $last_order_id, $card_ref ) {
		$user_id    = (int) $user_id;
		$product_id = (int) $product_id;
		$product    = wc_get_product( $product_id );
		$last_order = wc_get_order( $last_order_id );
		$order      = wc_create_order();
		$order->add_product( $product );
		$order->set_address( $last_order->get_address( 'billing' ), 'billing' );
		try {
			$order->set_currency( $last_order->get_currency() );
			$order->set_customer_id( $user_id );
		} catch ( WC_Data_Exception $ex ) {
			$msg = 'The function shuld not be called if conditions above cannot be satisfied.';
			throw new LogicException( $msg, 0, $ex );
		}
		$order->calculate_totals();

		$this->charge_card( $order, $card_ref );
	}

	/**
	 * Files_for_subscription.
	 *
	 * @param int $subscription_id Subscription id.
	 * @return array
	 */
	public static function files_for_subscription( $subscription_id ) {
		$subscription_id = (int) $subscription_id;
		$files           = get_post_meta( $subscription_id, 'p24_sub_files', true );
		if ( $files ) {
			return $files;
		} else {
			return array();
		}
	}

	/**
	 * Check subscription to extend.
	 */
	public function check_subscription_to_extend() {
		$critical_date = new DateTime( P24_Subscription_Config::days_to_renew() . ' days' );
		$todo          = P24_Subscription_Db::find_subscription_to_extends( $critical_date );
		$one           = array_shift( $todo );
		if ( $one ) {
			$this->create_subscription( $one->product_id, $one->user_id, $one->last_order_id, $one->card_ref );
			P24_Subscription_Db::mark_checked( $one->id );
		}
		if ( $todo ) {
			wp_schedule_single_event( time() + 60, 'p24_extra_do_subscription' );
		}
	}

	/**
	 * Bind events.
	 */
	public function bind_core_events() {
		if ( ! P24_Subscription_Config::is_active() ) {
			/* Do not bind any events. */
			return;
		}

		add_filter( 'product_type_selector', array( $this, 'register_product_type' ) );
		add_filter( 'woocommerce_product_class', array( $this, 'subscription_class_detector' ), 10, 2 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render_subscription_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_subscription_fields' ), 20, 2 );
		add_action( 'woocommerce_p24_subscription_add_to_cart', array( $this, 'render_subscription_buy_form' ), 30 );
		add_action( 'woocommerce_payment_complete', array( $this, 'check_for_subscriptions_in_order' ), 10, 1 );
		add_action( 'p24_payment_complete', array( $this, 'add_card_ref' ), 10, 2 );
		add_action( 'p24_daily_event', array( $this, 'check_subscription_to_extend' ) );
		add_action( 'p24_extra_do_subscription', array( $this, 'check_subscription_to_extend' ) );

		$this->for_user->bind_core_events();
		$this->for_admin->bind_core_events();
		$this->api->bind_core_events();
	}
}
