<?php
/**
 * File that define P24_Subscription_User class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class P24_Subscription_User
 */
class P24_Subscription_User {

	/**
	 * The P24_Core instance.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * The constructor.
	 *
	 * @param P24_Core $plugin_core The P24_Core instance.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
	}

	/**
	 * Extend nav.
	 *
	 * @param array $endpoints Default endpoints.
	 * @param array $descriptions Additional endpoints description.
	 * @return array
	 */
	public function extend_nav( $endpoints, $descriptions ) {
		$endpoints['p24-subscriptions-user'] = __( 'Subskrypcje P24' );

		return $endpoints;
	}

	/**
	 * Set nav url.
	 *
	 * @param string $url Proposed url.
	 * @param string $endpoint Endpoint name.
	 * @param string $value Unused in our context.
	 * @param string $permalink Unused in our context.
	 * @return string
	 */
	public function set_nav_url( $url, $endpoint, $value, $permalink ) {
		if ( 'p24-subscriptions-user' === $endpoint ) {
			$post = get_post( P24_Subscription_Config::page_id() );
			if ( $post ) {
				$url = get_post_permalink( $post );
			}
		}

		return $url;
	}

	/**
	 * Get post data.
	 *
	 * There are problems with arrays in $_POST. This is workaround.
	 */
	private function get_post() {
		/* We are forced to verify nonce in this place. */
		if ( isset( $_POST['p24_nonce'] ) ) {
			$nonce = sanitize_key( $_POST['p24_nonce'] );
			if ( ! wp_verify_nonce( $nonce, 'p24_action' ) ) {
				return array();
			}
		}
		return $_POST;
	}

	/**
	 * Process form.
	 *
	 * @param WP_User $user The active user.
	 * @param array   $cards The user cards.
	 */
	private function process_form( $user, $cards ) {
		$valid_keys = array_map(
			function ( $card ) {
				return $card->custom_key;
			},
			$cards
		);
		$post       = $this->get_post();
		if ( ! $post ) {
			return;
		}
		if ( ! array_key_exists( 'p24_action_type_field', $post ) ) {
			return;
		} elseif ( 'user_subscriptions' !== $post['p24_action_type_field'] ) {
			return;
		} elseif ( ! array_key_exists( 'card_for_subscription', $post ) ) {
			return;
		}
		$cards_for_subscription = wp_unslash( $post['card_for_subscription'] );
		foreach ( $cards_for_subscription as $subscription_id => $card_ref ) {
			if ( in_array( $card_ref, $valid_keys, true ) ) {
				P24_Subscription_Db::update_card_reference( (int) $subscription_id, $card_ref );
			} else {
				P24_Subscription_Db::clear_card_reference( (int) $subscription_id );
			}
		}
	}

	/**
	 * Render.
	 */
	public function render() {
		$user = wp_get_current_user();
		if ( $user->ID ) {
			$cards = WC_Gateway_Przelewy24::get_all_cards( (int) $user->ID );
			$this->process_form( $user, $cards );
			$subscriptions = P24_Subscription_Db::get_active_list_for_user( $user );
			$files         = $this->get_all_files_for_user( $user );
			$inactive      = P24_Subscription_Db::get_inactive_list_for_user( $user );
		} else {
			$cards         = array();
			$subscriptions = array();
			$files         = array();
			$inactive      = array();
		}

		$data = compact( 'user', 'subscriptions', 'cards', 'files', 'inactive' );
		$this->plugin_core->render_template( 'subscriptions-user', $data );
	}

	/**
	 * Find file.
	 *
	 * @param WP_User $user Use of interest.
	 * @param int     $subscription_id Subscription id.
	 * @param string  $file_name File name.
	 * @return array|null
	 */
	private function find_file( $user, $subscription_id, $file_name ) {
		$subscription_id = (int) $subscription_id;
		$file_name       = (string) $file_name;
		$subscriptions   = P24_Subscription_Db::get_active_list_for_user( $user );
		foreach ( $subscriptions as $subscription ) {
			if ( (int) $subscription->record_id === $subscription_id ) {
				$files = P24_Subscription::files_for_subscription( $subscription->product_id );
				foreach ( $files as $file ) {
					if ( $file['name'] === $file_name ) {
						return $file;
					}
				}
				return null;
			}
		}

		return null;
	}

	/**
	 * Get all files for user.
	 *
	 * @param WP_User $user Use of interest.
	 * @return array
	 */
	private function get_all_files_for_user( $user ) {
		$all_files     = array();
		$subscriptions = P24_Subscription_Db::get_active_list_for_user( $user );
		foreach ( $subscriptions as $subscription ) {
			$files = P24_Subscription::files_for_subscription( $subscription->product_id );
			array_walk(
				$files,
				function ( &$record ) use ( $subscription ) {
					$record['parent_id'] = $subscription->record_id;
					$record['name_url']  = rawurlencode( $record['name'] );
				}
			);
			$all_files = array_merge( $all_files, $files );
		}

		return $all_files;
	}

	/**
	 * Try download.
	 *
	 * @throws LogicException The callee should finish script.
	 */
	public function try_download() {
		global $current_user;
		if ( ! $current_user || ! $current_user->ID ) {
			return;
		}
		wp_verify_nonce( null );
		if ( isset( $_GET['subscription_id'] ) && isset( $_GET['file_name'] ) && isset( $_GET['p24'] ) && isset( $_GET['download'] ) ) {
			$subscription_id = sanitize_text_field( wp_unslash( $_GET['subscription_id'] ) );
			$file_name       = sanitize_text_field( wp_unslash( $_GET['file_name'] ) );
			$file            = $this->find_file( $current_user, $subscription_id, $file_name );
			if ( $file ) {
				WC_Download_Handler::download_file_xsendfile( $file['url'], $file['name'] );
				exit();
			}
		}
	}

	/**
	 * Bind events.
	 */
	public function bind_core_events() {
		add_filter( 'woocommerce_account_menu_items', array( $this, 'extend_nav' ), 10, 2 );
		add_filter( 'woocommerce_get_endpoint_url', array( $this, 'set_nav_url' ), 10, 4 );
		add_action( 'init', array( $this, 'try_download' ) );
		add_shortcode( 'p24_user_subscription', array( $this, 'render' ) );
	}
}
