<?php
/**
 * PayU Payment Gateway
 *
 * Provides a PayU Payment Gateway.
 *
 * @class WC_Gateway_PayU
 * @package WooCommerce
 * @category Payment Gateways
 * @author Inspire Labs
 *
 */

if ( class_exists( 'WC_Payment_Gateway' ) ) {

	class WC_Gateway_Payu extends WC_Payment_Gateway_CC {

		const PAYU_OPTIONS_COVERTED = 'payu_options_converted';

		public $wc_pre_30;

		/*
				public $server_id = array(
					'91.194.188.90',
					'91.194.188.144',
					'91.194.188.180',
					'91.194.189.28',
					'91.194.189.29',
					'5.134.215.4',
					'5.134.215.5',
					'91.194.189.67',
					'91.194.189.68',
					'91.194.189.69',
					'5.134.215.67',
					'5.134.215.68',
					'5.134.215.69',
					'185.68.12.10',
					'185.68.12.11',
					'185.68.12.12',
					'185.68.12.26',
					'185.68.12.27',
					'185.68.12.28'
				);
		*/

		public $liveurl;
		public $getStatusUrl;

		/**
		 *
		 * @var WPDesk_PayU_Settings
		 */
		protected $payu_settings;

		protected $debug = false;

		/** @var WPDesk_PayU_Rest_API */
		private $rest_api = false;

		/**
		 * __construct public function.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			$this->wc_pre_30 = version_compare( WC_VERSION, '3.0.0', '<' );

			$this->id           = 'payu';
			$this->method_title = __( 'PayU', 'woocommerce_payu' );
			$this->icon         = $this->plugin_url() . '/assets/images/icon.png';
			$this->has_fields   = false;

			// Load the settings.
			$this->init_settings();
			$this->payu_settings = new WPDesk_PayU_Settings( $this->settings );
			$this->enabled = ($this->is_currency_valid() && $this->payu_settings->is_gateway_enabled() ) ? 'yes' : 'no';

			$this->title       = $this->payu_settings->get_gateway_checkout_title();
			$this->description = $this->payu_settings->get_gateway_checkout_description();

			if ( $this->payu_settings->is_sandbox() ) {
				$this->liveurl      = 'https://secure.snd.payu.com/paygw/UTF/NewPayment';
				$this->getStatusUrl = 'https://secure.snd.payu.com/paygw/UTF/Payment/get/txt';
			} else {
				$this->liveurl      = 'https://www.platnosci.pl/paygw/UTF/NewPayment';
				$this->getStatusUrl = 'https://www.platnosci.pl/paygw/UTF/Payment/get/txt';
			}

			if ( $this->payu_settings->is_rest_api() ) {

				$this->supports = [
					'products',
					'refunds',
				];

				$this->rest_api = new WPDesk_PayU_Rest_API($this->payu_settings);
			}

			$this->convert_old_data();

			// Load the form fields.
			$this->init_form_fields();

			$this->hooks();
		} // End Constructor

		protected function hooks(){
			// Actions

			add_action( 'woocommerce_api_' . strtolower( get_class($this) ), [ $this, 'check_payu_response' ] );
			add_action( 'woocommerce_receipt_payu', [ $this, 'receipt_page' ] );
			add_action( 'woocommerce_update_options_payment_gateways', [ $this, 'process_admin_options' ] );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [
					$this,
					'process_admin_options'
			] );
		}

		private function convert_old_data()
		{
			if (get_option(self::PAYU_OPTIONS_COVERTED) != 1) {
				$key = $this->get_option_key();
				$settings = get_option($key);
				if (is_array($settings)) {
					$settings[WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_POS] = serialize([
						WPDesk_PayU_Settings::DEFAULT_CURRENCY => [
							WPDesk_PayU_Settings_POS::POS_FIELD_ID => ($settings['pos_id'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_MD5_KEY_1 => ($settings['key_1'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_MD5_KEY_2 => ($settings['key_2'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_AUTH_KEY => ($settings['pos_auth_key'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_CLIENT_ID => ($settings['client_id'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_CLIENT_SECRET => ($settings['client_secret'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_ID => ($settings['sandbox_pos_id'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_MD5_KEY_1 => ($settings['sandbox_key_1'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_MD5_KEY_2 => ($settings['sandbox_key_2'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_AUTH_KEY => ($settings['sandbox_pos_auth_key'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_CLIENT_ID => ($settings['sandbox_client_id'] ?? ''),
							WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_CLIENT_SECRET => ($settings['sandbox_client_secret'] ?? ''),
						]
					]);
				}
				$this->settings = $settings;
				update_option($key, $settings);
				update_option(self::PAYU_OPTIONS_COVERTED, 1);
			}
		}

		public function is_currency_valid( string $currency = '' ):bool
		{
			$currency = empty($currency)? get_woocommerce_currency(): $currency;
			return $this->payu_settings->is_currency_suported($currency);
		}

		/**
		 *
		 * @param array $data
		 *
		 * @return string Signature
		 *
		 * @since 1.0.0
		 */
		public function generateFormSig( $data, string $md5_key_1 ) {
			$sig = $data['pos_id'];
			$sig .= isset( $data['pay_type'] ) ? $data['pay_type'] : '';
			$sig .= $data['session_id'];
			$sig .= $data['pos_auth_key'];
			$sig .= $data['amount'];

			$sig .= $data['desc'];
			$sig .= $data['order_id'];
			$sig .= $data['first_name'];
			$sig .= $data['last_name'];

			$sig .= $data['street'];
			$sig .= $data['city'];

			$sig .= $data['post_code'];
			$sig .= $data['email'];
			$sig .= $data['phone'];
			$sig .= $data['language'];

			$sig .= $data['client_ip'];

			$sig .= $data['ts'];
			$sig .= $md5_key_1;

			return md5( $sig );
		}

		/**
		 * @param WC_Order $order
		 * @param bool $ia
		 * @param bool $subs
		 * @param array $payu_card_data
		 * @param bool|string $recurring
		 *
		 * @return array|false|mixed|object|string
		 * @throws Exception
		 */
		protected function create_payu_order(
			$order,
			$ia = false,
			$subs = false,
			$payu_card_data = [],
			$recurring = false
		) {

			$amount = $order->get_total();
			if ( $order->get_total() == 0 ) {
				$order->add_order_note( __( 'Płatność PayU - płatność testowa.',
					'woocommerce_payu' ) );
				$amount = 1;
			}

			if ( $recurring ) {
				$recurring_label = sprintf( __( 'płatność cykliczna - %1$s', 'woocommerce_payu' ), $recurring );
			} else {
				$recurring_label = __( 'płatność standardowa', 'woocommerce_payu' );
			}
			$payu_order_id = wpdesk_get_order_id( $order );
			$payu_order    = wpdesk_get_order_meta( $order, '_payu_order', true );
			if ( $payu_order != '' ) {
				$payu_order_id .= ' ' . current_time( 'mysql' );
			}
			$payu_order = $this->rest_api->create_order( $order, $payu_order_id, $ia, $subs, $payu_card_data, $recurring, $amount );

			wpdesk_update_order_meta( $order, '_payu_order', $payu_order );
			wpdesk_update_order_meta( $order, '_payu_order_id', $payu_order['orderId'] );
			/*
			 * This meta is used for invoice integration. Do not delete!
			 */
			wpdesk_update_order_meta( $order, '_transaction_id', $payu_order['orderId'] );
			$order->add_order_note(
				sprintf(
					__( 'Utworzone zamówienie w PayU. ID zamówienia PayU: %1$s. Na kwotę: %2$s %3$s. Typ: %4$s', 'woocommerce_payu' ),
					$payu_order['orderId'],
					$amount,
					wpdesk_get_order_meta( $order, '_currency', true ),
					$recurring_label
				)
			);
			if ( WC()->cart ) {
				WC()->cart->empty_cart();
			}
			return $payu_order;
		}

		protected function save_payu_single_data( WC_Order $order, $field_name ) {
			$value = '';
			if ( isset( $_POST[ $field_name ] ) ) {
				$value = $_POST[ $field_name ];
			}
			wpdesk_update_order_meta( $order, '_' . $field_name, $value );
		}

		protected function add_payu_card_data( $payu_card_data, $field_name ) {
			if ( isset( $_POST[ 'payu_' . $field_name ] ) ) {
				$payu_card_data[ $field_name ] = $_POST[ 'payu_' . $field_name ];
			}

			return $payu_card_data;
		}

		protected function save_payu_data( WC_Order $order ) {
			if ( isset( $_POST['payu_subscription'] ) ) {
				$payu_subscription = $_POST['payu_subscription'];
			} else {
				$payu_subscription = '0';
			}
			wpdesk_update_order_meta( $order, '_payu_subscription', $payu_subscription );
			if ( $payu_subscription == '1' ) {
				$payu_card_data = [];
				$payu_card_data = $this->add_payu_card_data( $payu_card_data, 'token_type' );
				$payu_card_data = $this->add_payu_card_data( $payu_card_data, 'value' );
				$payu_card_data = $this->add_payu_card_data( $payu_card_data, 'masked_card' );
				$payu_card_data = $this->add_payu_card_data( $payu_card_data, 'type' );
				wpdesk_update_order_meta( $order, '_payu_card_data', $payu_card_data );
			} else {
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					delete_post_meta( $order->get_id(), '_payu_card_data' );
				} else {
					$order->delete_meta_data( '_payu_card_data' );
					$order->save();
				}
			}
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function process_payment( $order_id ) {
			global $woocommerce;

			$order = wc_get_order( $order_id );

			$ia = false;
			if ( $this->id == 'payu_ia' ) {
				$ia = true;
			}


			/*
						$subs = false;
						$payu_card_data = array();

						if ( get_class( $order ) == 'WC_Subscription'  ) {
							$redirect_url = $order->get_view_order_url();
							wpdesk_update_order_meta( $order, '_payu_order', '' );
							return array(
								'result'   => 'success',
								'redirect' => $redirect_url
							);
						}


						if ( $this->is_subscription( $order_id ) ) {
							$this->save_payu_data( $order );
							if ( $order->get_total() == 0 ) {
								$order->add_order_note( __( 'Płatność PayU zatwierdzona - bezpłatny okres próbny.', 'woocommerce_payu' ) );
								wpdesk_update_order_meta( $order, '_payu_payment_completed', 1 );
								$order->payment_complete();
								//$order->update_status( 'completed' );
								if ( !$this->wc_pre_30 ) {
									$order->save();
								}

								// Also store it on the subscriptions being purchased or paid for in the order
								if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {
									$subscriptions = wcs_get_subscriptions_for_order( $order_id );
								} elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {
									$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );
								} else {
									$subscriptions = array();
								}

								$payu_card_data = wpdesk_get_order_meta( $order, '_payu_card_data', true );

								foreach ( $subscriptions as $subscription ) {
									$subscription_id = $this->wc_pre_30 ? $subscription->id : $subscription->get_id();
									update_post_meta( $subscription_id, '_payu_card_data', $payu_card_data );
								}

								$redirect_url = $order->get_checkout_order_received_url();
								return array(
									'result'   => 'success',
									'redirect' => $redirect_url
								);
							}
						}
			*/
			if ( $order->get_status() != 'pending' ) {
				$order->set_status( 'pending' );
			}

			// if ( $order->get_total() == 0 ) {
			// 	$order->add_order_note( __( 'Płatność PayU zatwierdzona - bezpłatny okres próbny.',
			// 		'woocommerce_payu' ) );
			// 	wpdesk_update_order_meta( $order, '_payu_payment_completed', 1 );
			// 	$order->payment_complete();
			// 	if ( ! $this->wc_pre_30 ) {
			// 		$order->save();
			// 	}
			// 	$redirect_url = $order->get_checkout_order_received_url();
			// 	return [
			// 		'result'   => 'success',
			// 		'redirect' => $redirect_url
			// 	];
			// }

			if ( isset( $this->settings['api_version'] ) && $this->settings['api_version'] == 'rest_api' ) {
				try {
					$payu_order = $this->create_payu_order( $order, $ia );
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
					return [
						'result' => 'failure',
					];
				}

				$redirect_url = $payu_order['redirectUri'];
				return [
					'result'   => 'success',
					'redirect' => $redirect_url
				];

			} else {
				// Remove cart
				$woocommerce->cart->empty_cart();
				return [
					'result'   => 'success',
					'redirect' => $order->get_checkout_payment_url( true )
				];
			}

		} // End process_payment()


		/**
		 * Receipt page.
		 * Display text and a button to direct the user to the payment screen.
		 *
		 * @param WC_Order $order
		 *
		 * @since 1.0.0
		 */
		public function receipt_page( $order ) {
			echo '<p>' . __( 'Dziękujemy za złożenie zamówienia. Kliknij aby dokonać płatności przez PayU.',
					'woocommerce_payu' ) . '</p>';
			echo $this->generate_payu_form( $order );
		} // End receipt_page()

		/**
		 * @param WC_Order $order
		 *
		 * @return bool
		 */
		public function update_order_status_from_payu( WC_Order $order ) {
			$data['ts']         = time();
			$pos = $this->payu_settings->get_pos_from_currency( $order->get_currency() );
			$data['pos_id']     = $pos->get_pos_id();
			$data['session_id'] = empty( $_POST['session_id'] ) ? wpdesk_get_order_meta( $order, '_order_key',
				true ) : $_POST['session_id'];
			$data['sig']        = md5( $pos->get_pos_id() . $data['session_id'] . $data['ts'] . $pos->get_md5_key_1() );

			if ( $this->debug ) {
				$debug = "\nRequest:" . print_r( $data, true ) . "\n";
				file_put_contents( 'pay-u.txt', $debug, FILE_APPEND );
			}

			$response = wp_remote_post( $this->getStatusUrl, [
				'method'    => 'POST',
				'body'      => $data,
				'timeout'   => 100,
				'sslverify' => false
			] );

			$responseTempArray = explode( "\n", $response['body'] );
			$responseArray     = [];
			foreach ( $responseTempArray as $value ) {
				$value                              = explode( ':', $value );
				$responseArray[ trim( $value[0] ) ] = trim( $value[1] );
			}

			// check response sig
			$sig = md5( $responseArray['trans_pos_id'] . $responseArray['trans_session_id'] . $responseArray['trans_order_id'] . $responseArray['trans_status'] . $responseArray['trans_amount'] . $responseArray['trans_desc'] . $responseArray['trans_ts'] . $pos->get_md5_key_2() );

			if ( $this->debug ) {
				$debug = implode( '   ', $data ) . "\n<br>";
				$debug .= 'sig: ' . $sig . "\n<br>" . "\n<br>";
				$debug .= print_r( $responseArray, true );
				file_put_contents( 'pay-u.txt', $debug, FILE_APPEND );
				wp_mail( 'debug@inspirelabs.pl',
					'PayU debug' . $_SERVER['HTTP_HOST'] . ' stamp:' . date( 'Y-m-d G:i:s' ), $debug );
			}

			if ( $responseArray['trans_sig'] == $sig ) {
				$order_id = wpdesk_get_order_id( $order );
				if ( empty( $order_id ) ) {
					$order = new WC_Order( $responseArray['trans_order_id'] );
				}
				if ( $order->get_status() != 'completed' ) {
					// set status from payu
					$this->update_order_status( $order, $responseArray['trans_status'] );
				}

				return true;
			} else {
				return false;
			}
		}

		/**
		 *
		 * @param WC_Order $order
		 * @param string $status
		 * @return void
		 * @throws Exception
		 */
		public function update_order_status( \WC_Order $order,  $status ){
			switch ( $status ) {
				case 1:
					$order->update_status( 'pending' );
					break;

				case 2:
					//$order->update_status('cancelled');
					$order->update_status( 'failed' );
					break;

				case 3:
					$order->update_status( 'failed' );
					break;

				case 4:
					$order->update_status( 'pending' );
					break;

				case 5:
					$order->update_status( 'processing' );
					break;

				case 7:
					$order->update_status( 'failed' );
					break;

				case 99:
					if ( wpdesk_get_order_meta( $order, '_payu_payment_completed', true ) == '' ) {
						$order->add_order_note( __( 'Płatność PayU zatwierdzona.', 'woocommerce_payu' ) );
						wpdesk_update_order_meta( $order, '_payu_payment_completed', 1 );
						$order->payment_complete();
					}
					break;

				case 888:
					$order->update_status( 'on-hold' );
					break;

			}
		}

		/**
		 * Is that notification api or live person
		 *
		 * @return bool
		 */
		protected function is_api_speaking_to_as() {
			return empty( $_GET[ WPDesk_PayU_Rest_API::PARAM_NAME_HASH ] );
		}

		/**
		 * Is there any error info in GET callback
		 * Note that the $_REQUEST is beign used. That is because error var in $_GET is overriden.
		 *
		 * @return bool
		 */
		private function is_return_callback_error() {
			return ! empty( $_REQUEST[ WPDesk_PayU_Rest_API::PARAM_NAME_ERROR ] );
		}

		/**
		 * Handle response for live person
		 * Note that the $_REQUEST is beign used. That is because error var in $_GET is overriden.
		 *
		 * @param WC_Order $order
		 */
		protected function handle_rest_api_person_response( WC_Order $order ) {
			if ( $this->rest_api->is_hash_valid( $order, $_GET[ WPDesk_PayU_Rest_API::PARAM_NAME_HASH ] ) ) {
				if ( $this->is_return_callback_error() ) {
					$this->update_order_error_status( $order, $_REQUEST[ WPDesk_PayU_Rest_API::PARAM_NAME_ERROR ] );
				}
			}
			$redirect_url = (get_class($order) == 'WC_Subscription')? $order->get_view_order_url() : $this->get_return_url( $order );
			wp_redirect( $redirect_url );
		}

		/**
		 * Process order status from PayU
		 *
		 * @param WC_Order $order
		 * @param string $payu_status
		 * @param bool $is_trial
		 */
		protected function process_order_status( WC_Order $order, $payu_status, $is_trial = false ) {
			if ( in_array( $order->get_status(), array( 'pending', 'failed' ) ) ) {
				if ( $payu_status === 'COMPLETED' ) {
					$order->add_order_note( __( 'Płatność PayU została potwierdzona.',
						'woocommerce_payu' ) );
					$order->payment_complete();
					if( $is_trial === true ){
						try{
							$this->process_refund( $order->get_id(), 1,  __('Zwrot za płatność testową karty', 'woocommerce_payu') );
							$order->add_order_note( __('Zwrot za płatność testową karty został przesłany do PayU', 'woocommerce_payu'));
						}catch(\Exception $e){
							$order->add_order_note( __( 'Wystąpił problem z realizacją zwrotu za testową płatność. Komunikat błędu: '.$e->getMessage(), 'woocommerce_payu' ));
						}
					}
				}
				if ( $payu_status === 'CANCELED' ) {
					$order->update_status( 'failed', __( 'Anulowana płatność PayU.', 'woocommerce_payu' ) );
				}
				if ( $payu_status === 'REJECTED' ) {
					$order->update_status( 'failed', __( 'Odrzucona płatność PayU.', 'woocommerce_payu' ) );
				}
			}

			$order->save();
		}

		/**
		 * @param WC_Order $order
		 *
		 * @throws Exception
		 */
		protected function handle_rest_api_response( WC_Order $order ) {
			$body = file_get_contents( 'php://input' );
			$json = json_decode( $body, true );
			if ( is_array( $json ) && isset( $json['order'] ) && isset($json['order']['status']) && $json['order']['status'] !== 'PENDING' ) {
				$verify_payu_order = $this->rest_api->get_order( $order->get_currency(), $json['order']['orderId'] );
				if ( isset( $verify_payu_order['orders'] ) && isset( $verify_payu_order['orders'][0] ) ) {
					$payu_order = $verify_payu_order['orders'][0];
				} else {
					throw new Exception( __( 'Niepoprawne zamówienie PayU!', 'woocommerce_payu' ) );
				}
				$payu_requests = wpdesk_get_order_meta( $order, '_payu_requests', true );
				if ( $payu_requests == '' ) {
					$payu_requests = [];
				}
				$payu_requests[ current_time( 'timestamp' ) ] = $json;
				wpdesk_update_order_meta( $order, '_payu_requests', $payu_requests );
				$payu_order_id = wpdesk_get_order_meta( $order, '_payu_order_id', true );
				if ( $payu_order_id == '' ) {
					$payu_order_id = $payu_order['orderId'];
					wpdesk_update_order_meta( $order, '_payu_order_id', $payu_order_id );
				}
				$payu_status = $payu_order['status'];
				$is_trial = isset($_GET['order_type']) && $_GET['order_type'] === 'trial';
				$this->process_order_status( $order, $payu_status, $is_trial );
			}
		}

		/**
		 * Check for PayU Response and verify validity
		 * @throws Exception
		 * @since 1.0.0
		 */
		public function check_payu_response() {
			if ( $this->payu_settings->is_rest_api() && isset( $_GET['rest_api'] ) && $_GET['rest_api'] == '1' ) {
				$order = wc_get_order( $_GET['order_id'] );
				if ( $order && (!WC_Gateway_Payu_Recurring::is_order_subscription( $order->get_id() ) || $this->get_option( 'payu_subscriptions_enabled', 'no' ) == 'no' ) ) {
					if ( $this->is_api_speaking_to_as() ) {
						return $this->handle_rest_api_response( $order );
					} else {
						return $this->handle_rest_api_person_response( $order );
					}
				}
			} else {
				$woocommerce = Woocommerce::instance();

				$order = new WC_Order( (int) $_GET['orderId'] );
				$pos = $this->payu_settings->get_pos_from_currency( $order->get_currency() );

				if ( ! empty( $_POST['pos_id'] ) ) // if set, then it's report
				{
					if ( $_REQUEST['orderId'] )// && in_array($_SERVER['REMOTE_ADDR'], $this->server_id)) // from server)
					{

						$sig = md5( $pos->get_pos_id() . $_POST['session_id'] . $_POST['ts'] . $pos->get_md5_key_2() );


						if ( $this->debug ) {
							$debug = 'postsig: ' . $_POST['sig'] . ' mysig: ' . $sig . "\n<br>";
							$debug .= implode( ' ', [
									$pos->get_pos_id(),
									$_POST['session_id'],
									$_POST['ts'],
									$pos->get_md5_key_2()
								] ) . "\n<br>";
							file_put_contents( 'pay-u.txt', $debug, FILE_APPEND );
							wp_mail( 'debug@inspirelabs.pl',
								'PayU debug' . $_SERVER['HTTP_HOST'] . ' stamp:' . date( 'Y-m-d G:i:s' ), $debug );
						}

						//$sig = $_POST['sig'];

						if ( $sig == $_POST['sig'] ) // if sig ok, change status
						{
							if ( $this->update_order_status_from_payu( $order ) ) {
								if ( $this->debug ) {
									$debug = 'OK';
									file_put_contents( 'pay-u.txt', $debug, FILE_APPEND );
									//file_put_contents('pay-u.txt', 'BUFFER:'. ob_get_contents() .';',  FILE_APPEND);
									wp_mail( 'debug@inspirelabs.pl',
										'PayU debug' . $_SERVER['HTTP_HOST'] . ' stamp:' . date( 'Y-m-d G:i:s' ),
										$debug );
								}
								die( 'OK' ); // info from payu acknowledged

							} else {
								if ( $this->debug ) {
									$debug = 'wrong SIG 2';
									file_put_contents( 'pay-u.txt', $debug, FILE_APPEND );
									wp_mail( 'debug@inspirelabs.pl',
										'PayU debug' . $_SERVER['HTTP_HOST'] . ' stamp:' . date( 'Y-m-d G:i:s' ),
										$debug );
								}
								die( 'WRONG SIG 2' );
							}

						} else {
							if ( $this->debug ) {
								$debug = 'wrong SIG 1';
								file_put_contents( 'pay-u.txt', $debug, FILE_APPEND );
								wp_mail( 'debug@inspirelabs.pl',
									'PayU debug' . $_SERVER['HTTP_HOST'] . ' stamp:' . date( 'Y-m-d G:i:s' ), $debug );
							}
							die( 'WRONG SIG 1' );
						}
					} else {
						die( 'WRONG IP OR ID' );
					}

				} else { // it's user request

					if ( ! empty( $_GET['errorId'] ) ) // error request
					{
						$this->update_order_error_status( $order, $_GET['errorId'] );

					} else { // success request
						$this->update_order_status_from_payu( $order );
					}

					$woocommerce->cart->empty_cart();

					$redirect_url = (get_class($order) == 'WC_Subscription')? $order->get_view_order_url() : $this->get_return_url( $order );
					wp_redirect( $redirect_url );

				}
			}

		} // End check_payu_response()

		/**
		 * Change order status using error code
		 *
		 * @param WC_Order $order
		 * @param string|int $error_id
		 */
		protected function update_order_error_status( WC_Order $order, $error_id ) {
			$statusData = $this->get_order_error_status( $error_id );

			if ( ! empty( $statusData['tstatus'] ) ) {
				$order->update_status( $statusData['tstatus'] );
			}
			if ( ! empty( $statusData['tmsg'] ) ) {
				$order->add_order_note( $statusData['tmsg'] );
				wc_add_notice( $statusData['tmsg'], 'error' );
			} else {
				$order->add_order_note( __( 'Płatności PayU: błąd',
						'woocommerce_payu' ) . ' ' . $_GET['errorId'] );
			}
		}

		/**
		 * @param int $code
		 *
		 * @return array
		 */
		public function get_order_error_status( $code ) {
			$tstatus = null;
			$tmsg    = null;

			switch ( $code ) {
				case '100':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak lub błędna wartość parametru POS_ID', 'woocommerce_payu' );
					break;
				case '101':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru SESSION_ID', 'woocommerce_payu' );
					break;
				case '102':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru TS', 'woocommerce_payu' );
					break;
				case '103':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak lub błędna wartość parametru SIG', 'woocommerce_payu' );
					break;
				case '104':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '105':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '106':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '107':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '108':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '109':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '110':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '111':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '112':
					$tstatus = 'failed';
					$tmsg    = __( 'Błędny numer konta bankowego', 'woocommerce_payu' );
					break;
				case '113':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '114':
					$tstatus = 'failed';
					$tmsg    = __( 'Brak parametru', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '200':
					$tstatus = 'failed';
					$tmsg    = __( 'Chwilowy błąd PayU', 'woocommerce_payu' );
					break;
				case '201':
					$tstatus = 'failed';
					$tmsg    = __( 'Chwilowy błąd PayU, bazy danych', 'woocommerce_payu' );
					break;
				case '202':
					$tstatus = 'failed';
					$tmsg    = __( 'POS jest zablokowany', 'woocommerce_payu' );
					break;
				case '203':
					$tstatus = 'on-hold';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '204':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '205':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '206':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '207':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '208':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '209':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '500':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '501':
					$tstatus = 'failed';
					$tmsg    = __( 'Anulowana płatność PayU44.', 'woocommerce_payu' );
					break;
				case '502':
					//$tstatus = 'failed';
					$tmsg = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '503':
					//$tstatus = 'failed';
					$tmsg = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '504':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '505':
					//$tstatus = 'failed';
					$tmsg = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '506':
					//$tstatus = 'failed';
					$tmsg = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '507':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				case '508':
					$tstatus = 'failed';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' ) . ' ' . $code;
					break;
				default:
					$tstatus = 'on-hold';
					$tmsg    = __( 'Błąd', 'woocommerce_payu' );
					break;
			}

			return [ 'tstatus' => $tstatus, 'tmsg' => $tmsg ];
		}

		/**
		 * @param int $order_id
		 * @param null $amount
		 * @param string $reason
		 *
		 * @return bool
		 * @throws Exception
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			$order         = wc_get_order( $order_id );
			$payu_order_id = wpdesk_get_order_meta( $order, '_payu_order_id', true );
			$payu_refund   = $this->rest_api->refund( $order->get_currency(), $payu_order_id, $amount, $reason );
			$order->add_order_note( sprintf( __( 'Utworzony zwrot w PayU. ID zwrotu: %s. Kwota zwrotu: %s %s',
				'woocommerce_payu' ), $payu_refund['refund']['refundId'], $amount,
				wpdesk_get_order_meta( $order_id, '_currency', true ) ) );

			return true;
		}

				/**
		 * Initialise Gateway Settings Form Fields
		 *
		 * @since 1.0.0
		 */
		public function init_form_fields() {
			$protocol = is_ssl() ? 'https://' : 'http://';

			$ap_link = 'https://www.wpdesk.pl/sklep/aktywne-platnosci-woocommerce/';

			$s_ap1 = '';
			$s_ap2 = '';

			if ( wpdesk_is_plugin_active( 'woocommerce-active-payments/activepayments.php' ) ) {
				$s_ap1 = '<a href="' . admin_url( 'admin.php?page=woocommerce_activepayments' ) . '">';
				$s_ap2 = '</a>';
			}

			$this->form_fields = [
				WPDesk_PayU_Settings::FORM_FIELD_PAGE_TITLE => [
					'title'       => __( 'PayU', 'woocommerce_payu' ),
					'type'        => 'title',
					'description' => __( 'Bramka płatności przekierowuje kupującego na stronę PayU w celu dokonania płatności. <a href="https://www.wpdesk.pl/docs/payu-woocommerce-docs/" target="_blank">Instrukcja instalacji i konfiguracji wtyczki &rarr;</a>',
						'woocommerce_payu' ),
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_TITLE => [
					'title' => __( 'Ustawienia główne', 'woocommerce_payu' ),
					'type'  => 'title'
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_ENABLED => [
					'title'   => __( 'Włącz/Wyłącz', 'woocommerce_payu' ),
					'type'    => 'checkbox',
					'label'   => __( 'Włącz PayU', 'woocommerce_payu' ),
					'default' => 'yes'
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_CHECKOUT_TITLE => [
					'title'       => __( 'Tytuł', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Nazwa bramki płatności, którą klient widzi przy składaniu zamówienia.',
						'woocommerce_payu' ),
					'default'     => __( 'PayU', 'woocommerce_payu' )
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_CHECKOUT_DESCRIPTION => [
					'title'       => __( 'Opis', 'woocommerce_payu' ),
					'type'        => 'textarea',
					'description' => __( 'Opis bramki płatności, którą klient widzi przy składaniu zamówienia.',
						'woocommerce_payu' ),
					'default'     => __( 'Płatności online PayU. Zapłać przelewem elektronicznym, przelewem tradycyjnym lub kartą płatniczą.',
						'woocommerce_payu' ),
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_API_VERSION => [
					'title'   => __( 'Wybierz API', 'woocommerce_payu' ),
					'type'    => 'select',
					'options' => [
						'classic_api' => __( 'Classic API', 'woocommerce_payu' ),
						'rest_api'    => __( 'Rest API', 'woocommerce_payu' ),
					],
					'default' => 'rest_api'
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_TEST_MODE => [
					'title'       => __( 'Płatność testowa', 'woocommerce_payu' ),
					'type'        => 'checkbox',
					'label'       => __( 'Włącz płatność testową', 'woocommerce_payu' ),
					'description' => __( 'W ustawieniach punktu płatności PayU na swoim koncie włącz typ płatności testowa.',
						'woocommerce_payu' ),
					'default'     => 'no'
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_SANDBOX => [
					'title'       => __( 'Tryb testowy', 'woocommerce_payu' ),
					'type'        => 'checkbox',
					'label'       => __( 'Włącz tryb testowy (Sandbox)', 'woocommerce_payu' ),
					'description' => sprintf( __( 'Więcej informacji na temat %ssandbox%s.', 'woocommerce_payu' ),
						'<a href="http://developers.payu.com/pl/overview.html#sandbox">', '</a>' ),
					'default'     => 'no'
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_CHECK_SIG => [
					'title'       => __( 'Podpis sig', 'woocommerce_payu' ),
					'type'        => 'checkbox',
					'label'       => __( 'Zabezpieczaj moje transakcje/Sprawdzaj poprawność sig-a',
						'woocommerce_payu' ),
					'description' => __( 'Sig to ciąg znaków pełniący rolę podpisu cyfrowego. Weryfikacja poprawności sig-a zwiększa bezpieczeństwo transakcji.',
						'woocommerce_payu' ),
					'default'     => 'no'
				],

				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_POS  => [
					'title'       => __( 'Zarządzanie walutami', 'woocommerce_payu' ),
					'type'        => 'accounts'
				],

				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_IA_TITLE => [
					'title' => __( 'Raty PayU', 'woocommerce_payu' ),
					'type'  => 'title'
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_IA_ENABLED => [
					'title'       => __( 'Raty PayU', 'woocommerce_payu' ),
					'type'        => 'checkbox',
					'label'       => __( 'Włącz Raty PayU', 'woocommerce_payu' ),
					'default'     => 'no',
					'description' => sprintf( __( 'Zostanie utworzona nowa metoda płatności Raty PayU. Raty PayU używają również powyższych ustawień. Jeśli używasz wtyczki %sAktywne Płatności%s przejdź do %sustawień%s i przypisz Raty PayU do wybranych metod wysyłki.',
						'woocommerce_payu' ), '<a target="_blank" href="' . $ap_link . '">', '</a>', $s_ap1, $s_ap2 ),
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_IA_CHECKOUT_TITLE => [
					'title'       => __( 'Tytuł Raty PayU', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Nazwa bramki płatności, którą klient widzi przy składaniu zamówienia.',
						'woocommerce_payu' ),
					'default'     => __( 'PayU Raty', 'woocommerce_payu' )
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_IA_CHECKOUT_DESCRIPTION => [
					'title'       => __( 'Opis Raty PayU', 'woocommerce_payu' ),
					'type'        => 'textarea',
					'description' => __( 'Opis bramki płatności, którą klient widzi przy składaniu zamówienia.',
						'woocommerce_payu' ),
					'default'     => __( 'Raty są dostępne dla zakupów na łączną kwotę od 300 do 20 000 zł.',
						'woocommerce_payu' ),
				],

				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_SUBSCRIPTIONS_TITLE => [
					'title'       => __( 'Płatności cykliczne PayU', 'woocommerce_payu' ),
					'type'        => 'title',
					'description' => sprintf( __( '%sDowiedz się jak uruchomić płatności cykliczne →%s',
						'woocommerce_payu' ),
						'<a target="_blank" href="https://www.wpdesk.pl/docs/payu-woocommerce-docs/#Platnosci_cykliczne_PayU">',
						'</a>' ),
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_SUBSCRIPTIONS_ENABLED => [
					'title'       => __( 'Płatności cykliczne PayU', 'woocommerce_payu' ),
					'type'        => 'checkbox',
					'label'       => __( 'Włącz Płatności cykliczne PayU', 'woocommerce_payu' ),
					'default'     => 'no',
					'description' => sprintf( __( 'Zostanie utworzona nowa metoda płatności Płatności cykliczne PayU. Płatności cykliczne PayU używają również powyższych ustawień. Jeśli używasz wtyczki %sAktywne Płatności%s przejdź do %sustawień%s i przypisz metodę płatności do wybranych metod wysyłki.',
						'woocommerce_payu' ), '<a target="_blank" href="' . $ap_link . '">', '</a>', $s_ap1, $s_ap2 ),
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_SUBSCRIPTIONS_CHECKOUT_TITLE => [
					'title'       => __( 'Tytuł', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Nazwa bramki płatności, którą klient widzi przy składaniu zamówienia.',
						'woocommerce_payu' ),
					'default'     => __( 'PayU Płatność cykliczna', 'woocommerce_payu' )
				],
				WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_SUBSCRIPTIONS_CHECKOUT_DESCRIPTION => [
					'title'       => __( 'Opis', 'woocommerce_payu' ),
					'type'        => 'textarea',
					'description' => __( 'Opis bramki płatności, którą klient widzi przy składaniu zamówienia.',
						'woocommerce_payu' ),
					'default'     => __( 'Płatność cykliczna PayU będzie automatycznie realizowana przy każdym odnowieniu subskrypcji.',
						'woocommerce_payu' ),
				],

				WPDesk_PayU_Settings::FORM_FIELD_RETURNS_TITLE => [
					'title'       => __( 'Adresy powrotów', 'woocommerce_payu' ),
					'type'        => 'title',
					'description' => __( 'Skopiuj do konfiguracji punktu płatności PayU zgodnie z instrukcją konfiguracji wtyczki.',
						'woocommerce_payu' ),
				],
				WPDesk_PayU_Settings::FORM_FIELD_RETURN_ERROR => [
					'title'             => __( 'Błąd', 'woocommerce_payu' ),
					'type'              => 'text',
					'css'               => 'width:60em;',
					'default'           => str_replace( $protocol, '',
						site_url( '?wc-api=WC_Gateway_Payu&sessionId=%sessionId%&orderId=%orderId%&errorId=%error%' ) ),
					'custom_attributes' => [
						'readonly' => 'readonly'
					]
				],
				WPDesk_PayU_Settings::FORM_FIELD_RETURN_OK => [
					'title'             => __( 'Poprawnie', 'woocommerce_payu' ),
					'type'              => 'text',
					'css'               => 'width:60em;',
					'default'           => str_replace( $protocol, '',
						site_url( '?wc-api=WC_Gateway_Payu&sessionId=%sessionId%&orderId=%orderId%' ) ),
					'custom_attributes' => [
						'readonly' => 'readonly'
					]
				],
				WPDesk_PayU_Settings::FORM_FIELD_RETURN_REPORTS => [
					'title'             => __( 'Raporty', 'woocommerce_payu' ),
					'type'              => 'text',
					'css'               => 'width:60em;',
					'default'           => str_replace( $protocol, '',
						site_url( '?wc-api=WC_Gateway_Payu&sessionId=%sessionId%&orderId=%orderId%' ) ),
					'custom_attributes' => [
						'readonly' => 'readonly'
					]
				]
			];
		} // End init_form_fields()

		public function validate_accounts_field( $key, $value ) {
			$value = !is_array( $value ) ? [] : $value;

			foreach( $value as $currency => $pos ){
				foreach( $pos as $pos_key => $pos_value ){
					$value[$currency][$pos_key] = wp_kses_post( trim( stripslashes( $pos_value )));
				}
			}

			return serialize($value);
		}

		public function generate_accounts_html( $key, $data ) {
			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'title'             => '',
				'desc_tip'          => false,			);

			$data = wp_parse_args( $data, $defaults );

			$default = [
				get_woocommerce_currency() => []
			];
			$profiles = !empty($this->get_option( $key )) ? unserialize($this->get_option( $key )) : $default;

			ob_start();
			require 'views/payu-settings-pos.php';
			return ob_get_clean();
		}

		public function generate_single_pos( string $currency ) {

			$is_default_profile = false;
			$is_test_mode = false;
			$field_key = $this->get_field_key(WPDesk_PayU_Settings::FORM_FIELD_GATEWAY_POS);

			ob_start();
			require 'views/payu-settings-pos-item.php';
			return ob_get_clean();
		}

		/**
		 * Generate the PayU button link.
		 *
		 * @since 1.0.0
		 */
		public function generate_payu_form( $order_id ) {
			$order = new WC_Order( $order_id );
			$pos = $this->payu_settings->get_pos_from_currency( $order->get_currency() );

			$payment_method = wpdesk_get_order_meta( $order, '_payment_method', true );

			$payuform = "";


			// Merchant details
			$merchant = [
				'pos_id'       => $pos->get_pos_id(),
				'pos_auth_key' => $pos->get_auth_key(),
				'language'     => 'pl'
			];

			if ( $payment_method == 'payu_ia' ) {
				$merchant['pay_type'] = "ai";
			}

			// Customer details
			$customer = [
				'first_name' => wpdesk_get_order_meta( $order, '_billing_first_name', true ),
				'last_name'  => wpdesk_get_order_meta( $order, '_billing_last_name', true ),
				'email'      => wpdesk_get_order_meta( $order, '_billing_email', true ),
				'street'     => wpdesk_get_order_meta( $order, '_billing_address_1',
						true ) . ' ' . wpdesk_get_order_meta( $order, '_billing_address_2', true ),
				'city'       => wpdesk_get_order_meta( $order, '_billing_city', true ),
				'post_code'  => wpdesk_get_order_meta( $order, '_billing_postcode', true ),
				'phone'      => wpdesk_get_order_meta( $order, '_billing_phone', true ),
				'client_ip'  => $_SERVER['REMOTE_ADDR']
			];

			// Item details
			$item = [
				'desc'       => trim( mb_substr( sprintf( __( 'Order %s', 'woocommerce_payu' ),
					$order->get_order_number() ), 0, 45, 'UTF-8' ) ),
				'amount'     => $order->get_total() * 100,
				'order_id'   => $order_id,
				'session_id' => wpdesk_get_order_meta( $order, '_order_key', true )
			];

			if ( $this->payu_settings->is_test_mode() ) {
				$item['pay_type'] = 't';
			}

			$paramsArray = array_merge( $merchant, $customer, $item );

			if ( $this->payu_settings->is_check_sig() ) {
				$paramsArray['ts']  = time();
				$paramsArray['sig'] = $this->generateFormSig( $paramsArray, $pos->get_md5_key_1() );
			}

			foreach ( $paramsArray as $key => $value ) {
				if ( $value ) {
					$payuform .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
				}
			}

			$payuform .= '<input type="hidden" name="js" value="0" id="js_value" />' . "\n";

			$payuform .= '<script type="text/javascript">
			<!--
				document.getElementById("js_value").value = 1;
			-->
			</script>';


			// The form
			return '<form action="' . $this->liveurl . '" method="POST" name="payform" id="payform">
				' . $payuform . '
				<input type="submit" class="button" id="submit_payu_payment_form" value="' . __( 'Zapłać przez PayU',
					'woocommerce_payu' ) . '" /> <a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __( 'Anuluj zamówienie i przywróć koszyk',
					'woocommerce_payu' ) . '</a>
				<script type="text/javascript">
					jQuery(function(){
						jQuery("body").block(
							{
								message: "' . __( 'Dziękujemy za złożenie zamówienia. Przekierujemy Cię na stronę PayU w celu dokonania płatności.',
					'woocommerce_payu' ) . '",
								overlayCSS: {
									background: "#fff",
									opacity: 0.6
								},
								css: {
							        padding:        20,
							        textAlign:      "center",
							        color:          "#555",
							        border:         "3px solid #aaa",
							        backgroundColor:"#fff",
							        cursor:         "wait"
							    }
							});
						jQuery("#submit_payu_payment_form").click();
					});
				</script>
			</form>';
		} // End generate_payu_form()

				/**
		 * Get the plugin URL
		 *
		 * @since 1.0.0
		 */
		public function plugin_url() {
			if ( isset( $this->plugin_url ) ) {
				return $this->plugin_url;
			}
			if ( is_ssl() ) {
				return $this->plugin_url = str_replace( 'http://', 'https://',
						WP_PLUGIN_URL ) . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) );
			} else {
				return $this->plugin_url = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) );
			}
		} // End plugin_url()


		/**
		 * Admin Panel Options
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 *
		 * @since 1.0.0
		 */
		public function admin_options() {

			$ping_message = '';
			if ( !$this->rest_api ) {
				echo '<div class="error fade"><p>' . sprintf( __( 'Aby korzystać ze zwrotów użyj Rest API.',
						'woocommerce_payu' ) ) . '</p></div>' . "\n";
				if ( get_option( 'woocommerce_currency' ) != 'PLN' ) {
					echo '<div class="error fade"><p>' . sprintf( __( 'Uwaga! Classic API działa tylko dla waluty PLN!.',
							'woocommerce_payu' ) ) . '</p></div>' . "\n";
				}
			}

			if ( $ping_message ) {
				echo '<div class="error fade"><p>' . sprintf( __( 'Komunikat API PayU: %s', 'woocommerce_payu' ),
						$ping_message ) . '</p></div>' . "\n";
			}

			if ( 1 == 1 || get_option( 'woocommerce_currency' ) == 'PLN' ) {

				echo '<table class="form-table">';

				// Generate the HTML For the settings form.
				$this->generate_settings_html();
				?>
                </table><!--/.form-table-->
				<?php
			} else {
				?>
                <div class="inline error"><p>
                        <strong><?php _e( 'Bramka wyłączona',
								'woocommerce_payu' ); ?></strong> <?php echo sprintf( __( 'Wybierz walutę "Polski Złoty" w <a href="%s">ustawieniach sklepu</a>, aby włączyć bramkę PayU.',
							'woocommerce_payu' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) ); ?>
                    </p></div>
				<?php
			} // End check currency
		} // End admin_options()

		/**
		 * There are no payment fields for PayU, but we want to show the description if set.
		 *
		 * @since 1.0.0
		 */
		public function payment_fields() {
			if ( $this->description ) {
				echo wpautop( wptexturize( $this->description ) );
			}
		} // End payment_fields()


	} //  End Class

}
