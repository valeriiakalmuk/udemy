<?php
/**
 * File that define P24_Request_Support class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that analyse requests originated from WordPress but WooCommerce.
 *
 * It supports user mode and admin mode.
 * Requests from WooCommerce are supported in different places.
 *
 * The class interact with $_POST.
 */
class P24_Request_Support {

	const WP_JSON_GET_KEY_CURRENCY              = 'currency';
	const OPTION_KEY_FLAT                       = 'przelewy24_flat_settings';
	const OPTION_KEY_MULTI_CURRENCY_MULTIPLIERS = 'przelewy24_multi_currency_multipliers';
	const OPTION_KEY_MULTI_CURRENCY_FORMATS     = 'przelewy24_multi_currency_formats';
	const OPTION_KEY_COMMON                     = 'przelewy24_common_settings';
	const KEY_CHANGES_CURRENCY                  = 'currency';
	const KEY_NEW_STATUS                        = 'new_status';

	/**
	 * Set of changes.
	 *
	 * @var array
	 */
	private $changes = array();

	/**
	 * Return changes of active currency.
	 *
	 * @return string|null
	 */
	public function get_currency_changes() {
		return ( array_key_exists( self::KEY_CHANGES_CURRENCY, $this->changes ) )
			? $this->changes[ self::KEY_CHANGES_CURRENCY ] : null;
	}

	/**
	 * Analyse if there is new currency to set.
	 *
	 * @param array $data Array of data to analyse.
	 */
	private function preload_currency_changes( $data ) {
		if ( isset( $data['p24_currency'] ) ) {
			$this->changes[ self::KEY_CHANGES_CURRENCY ] = $data['p24_currency'];
		}
	}

	/**
	 * Get changes of format.
	 *
	 * @return array|null
	 */
	public function get_format_changes() {
		if ( array_key_exists( 'formats', $this->changes ) ) {
			return $this->changes['formats'];
		} else {
			return null;
		}
	}

	/**
	 * Analyse if there are new formats to set.
	 *
	 * @param array $data Array of data to analyse.
	 */
	private function preload_format_changes( $data ) {
		if ( array_key_exists( 'p24_currency', $data ) && array_key_exists( 'p24_formats', $data ) ) {
			$this->changes['formats'] = array( $data['p24_currency'] => $data['p24_formats'] );
		}
	}

	/**
	 * Get changes of multipliers.
	 *
	 * @return array|null
	 */
	public function get_multipliers_changes() {
		if ( array_key_exists( 'p24_multiplers', $this->changes ) ) {
			return $this->changes['p24_multiplers'];
		} else {
			return null;
		}
	}

	/**
	 * Analyse if there are new multipliers to change.
	 *
	 * @param array $data Array of data to analyse.
	 * @throws LogicException If there is a bug in send data.
	 */
	private function preload_multipliers_changes( $data ) {
		if ( isset( $data['p24_multipliers'] ) ) {
			$multipliers = $data['p24_multipliers'];
		} else {
			$multipliers = array();
		}
		$default                         = P24_Woo_Commerce_Low_Level_Getter::get_unhooked_currency_form_woocommerce();
		$multipliers[ $default ]         = 1;
		$multipliers                     = array_map( 'floatval', $multipliers );
		$multipliers                     = array_filter( $multipliers );
		$this->changes['p24_multiplers'] = $multipliers;
	}

	/**
	 * Get list of common changes.
	 *
	 * @return mixed|null
	 */
	public function get_common_changes() {
		if ( array_key_exists( 'p24_common', $this->changes ) ) {
			return $this->changes['p24_common'];
		} else {
			return null;
		}
	}

	/**
	 * Get list of flat changes.
	 *
	 * @return array
	 */
	public function get_flat_changes() {
		if ( array_key_exists( self::OPTION_KEY_FLAT, $this->changes ) ) {
			return $this->changes[ self::OPTION_KEY_FLAT ];
		} else {
			return array();
		}
	}

	/**
	 * Get status changes.
	 *
	 * @return null|array
	 */
	public function get_order_status_changes() {
		if ( array_key_exists( self::KEY_NEW_STATUS, $this->changes ) ) {
			return $this->changes[ self::KEY_NEW_STATUS ];
		} else {
			return null;
		}
	}

	/**
	 * Analyse if activation of multi currency is set.
	 *
	 * @param array $data Array of data to analyse.
	 */
	private function preload_multi_currency_changes( $data ) {
		$active = array_key_exists( 'p24_multi_currency_active', $data ) && 'yes' === $data['p24_multi_currency_active'] ? 'yes' : 'no';
		$this->changes['p24_common']['p24_multi_currency'] = $active;
		if ( 'yes' === $active && array_key_exists( 'p24_reports_currency', $data ) ) {
			wc_setcookie( 'admin_p24_reports_currency', $data['p24_reports_currency'] );
		}

		$order_created_notification                                    = array_key_exists( 'p24_notification_order_created', $data ) && 'yes' === $data['p24_notification_order_created'] ? 'yes' : 'no';
		$this->changes['p24_common']['p24_notification_order_created'] = $order_created_notification;
	}

	/**
	 * Analyse if activation of multi currency is set.
	 *
	 * @param array $data Array of data to analyse.
	 */
	private function preload_activate_statuses( $data ) {
		$active = array_key_exists( 'p24_statuses_active', $data ) && $data['p24_statuses_active'];
		$value  = $active ? 'yes' : 'no';
		$this->changes[ self::OPTION_KEY_FLAT ]['p24_statuses_active'] = $value;
	}

	/**
	 * Preload add new status.
	 *
	 * @param array $data Post data.
	 */
	private function preload_add_status( $data ) {
		if ( isset( $data['p24-status-new-label'] ) && isset( $data['p24-status-new-code'] ) ) {
			$this->changes[ self::KEY_NEW_STATUS ] = array(
				'code'  => $data['p24-status-new-code'],
				'label' => $data['p24-status-new-label'],
			);
		}
	}

	/**
	 * Update subscription config.
	 *
	 * @param array $data Array of data to analyse.
	 */
	private function update_subscription_config( $data ) {
		$new_keys = P24_Subscription_Config::update_config( $data );
		if ( ! array_key_exists( self::OPTION_KEY_FLAT, $this->changes ) ) {
			$this->changes[ self::OPTION_KEY_FLAT ] = $new_keys;
		} else {
			$this->changes[ self::OPTION_KEY_FLAT ] += $new_keys;
		}
	}

	/**
	 * Process data from GET.
	 */
	private function check_get() {
		wp_verify_nonce( null );
		$get = $_GET;
		if ( isset( $get['p24_change_currency'] ) ) {
			$this->changes[ self::KEY_CHANGES_CURRENCY ] = $get['p24_change_currency'];
		}
	}

	/**
	 * Validate nonce and return request.
	 *
	 * @return null|array
	 */
	private function get_post_data() {
		if ( isset( $_POST['p24_nonce'] ) ) {
			$nonce = sanitize_key( $_POST['p24_nonce'] );
			if ( wp_verify_nonce( $nonce, 'p24_action' ) ) {
				return $_POST;
			}
		}
		return null;
	}

	/**
	 * Analyse the whole request.
	 *
	 * We need call this action very early.
	 */
	public function analyse() {
		$this->check_get();

		$data = $this->get_post_data();
		if ( ! $data ) {
			return;
		}

		if ( isset( $data['p24_action_type_field'] ) ) {
			$field = $data['p24_action_type_field'];
			switch ( $field ) {
				case 'change_currency':
					$this->preload_currency_changes( $data );
					break;
				case 'change_formats':
					$this->preload_currency_changes( $data );
					$this->preload_format_changes( $data );
					break;
				case 'change_multipliers':
					$this->preload_multipliers_changes( $data );
					break;
				case 'activate_multi_currency':
					$this->preload_multi_currency_changes( $data );
					break;
				case 'activate_statuses':
					$this->preload_activate_statuses( $data );
					break;
				case 'add_status':
					$this->preload_add_status( $data );
					break;
				case 'update_subscription':
					$this->update_subscription_config( $data );
					break;
			}
		}
	}

	/**
	 * Flush options to the database.
	 *
	 * This action should be done later thant analyse.
	 */
	public function flush_options() {
		$set_overwrite = array(
			self::OPTION_KEY_MULTI_CURRENCY_MULTIPLIERS => $this->get_multipliers_changes(),
		);
		$set_merge     = array(
			self::OPTION_KEY_MULTI_CURRENCY_FORMATS => $this->get_format_changes(),
			self::OPTION_KEY_COMMON                 => $this->get_common_changes(),
		);
		$set_flat      = $this->get_flat_changes();
		foreach ( $set_overwrite as $k => $v ) {
			if ( $v ) {
				update_option( $k, $v );
			}
		}
		foreach ( $set_merge as $k => $v ) {
			if ( $v ) {
				$v = $v + get_option( $k, array() );
				update_option( $k, $v );
			}
		}
		foreach ( $set_flat as $k => $v ) {
			if ( $v ) {
				update_option( $k, $v );
			}
		}
	}
}
