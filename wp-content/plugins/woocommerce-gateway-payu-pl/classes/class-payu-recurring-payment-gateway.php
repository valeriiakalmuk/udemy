<?php

/**
 * PayU Payment Gateway
 *
 * Provides a PayU Payment Gateway.
 *
 * @class    WC_Gateway_PayU
 * @package  WooCommerce
 * @category Payment Gateways
 * @author   Inspire Labs
 */

class WC_Gateway_Payu_Recurring extends WC_Gateway_Payu
{

    const RECURRING_PAYMENT_VALUE_FIRST     = 'FIRST';
    const RECURRING_PAYMENT_VALUE_FOLLOW_UP = 'STANDARD';

    /**
     * @var WC_Gateway_Payu 
     */
    private $payu_gateway = null;

    private $is_failed = false;

    public function __construct()
    {
        parent::__construct();
        $this->id           = 'payu_recurring';
        $this->method_title = __('PayU Płatności cykliczne', 'woocommerce_payu');
        $this->has_fields   = true;
        $this->enabled = ($this->is_currency_valid() && $this->payu_settings->is_subscription_gateway_enabled() ) ? 'yes' : 'no';

        $this->title       = $this->payu_settings->get_gateway_subscriptions_checkout_title();
        $this->description = $this->payu_settings->get_gateway_subscriptions_checkout_description();
        $this->icon        = $this->plugin_url() . '/assets/images/icon.png';

        if ($this->payu_settings->is_rest_api()) {
            $this->supports = [
            'products',
            'refunds',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions',
            ];
        } else {
            $this->enabled = 'no';
        }

        $this->hooks();
    } // End Constructor

    protected function hooks()
    {
        // add_filter( 'wcs_is_scheduled_payment_attempt', function(){
        //     return true;
        // });
        add_action('woocommerce_scheduled_subscription_payment_' . $this->id, [$this, 'scheduled_subscription_payment'], 10, 2);
        add_action('woocommerce_subscription_failing_payment_method_updated_' . $this->id, [$this, 'update_failing_payment_method'], 10, 2);
        // display the credit card used for a subscription in the "My Subscriptions" table
        add_filter('woocommerce_my_subscriptions_payment_method', [$this, 'maybe_render_subscription_payment_method'], 10, 2);
        add_action('woocommerce_api_' . strtolower(get_parent_class($this)), [$this, 'check_payu_response'], 99);
    }

    /**
     * Check for PayU Response and verify validity
     *
     * @throws Exception
     * @since  1.0.0
     */
    public function check_payu_response()
    {
        if ($this->payu_settings->is_rest_api() && isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
            $order = wc_get_order($_GET['order_id']);
            if ($order && WC_Gateway_Payu_Recurring::is_order_subscription($order->get_id())) {
                add_filter(
                    'wcs_is_scheduled_payment_attempt', function () {
                        return true;
                    }
                );

                if ($this->is_api_speaking_to_as()) {
                      return $this->handle_rest_api_response($order);
                } else {
                    return $this->handle_rest_api_person_response($order);
                }
            }
        }
    }

    /**
     * Update PayU data to complete a payment to make up for
     * an automatic renewal payment which previously failed.
     *
     * @access public
     *
     * @param WC_Subscription $subscription  The subscription for which the failing payment method relates.
     * @param WC_Order        $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
     *
     * @return void
     */
    public function update_failing_payment_method($subscription, $renewal_order)
    {
        $payu_card_data = wpdesk_get_order_meta($renewal_order, '_payu_card_data', true);
        $payu_order     = wpdesk_get_order_meta($renewal_order, '_payu_order', true);
        $subscription->update_meta_data('_payu_card_data', $payu_card_data);
        $subscription->update_meta_data('_payu_order', $payu_order);
    }

    public function maybe_render_subscription_payment_method($payment_method_to_display, $subscription)
    {
        // bail for other payment methods
        if ($this->id !== ($this->wc_pre_30 ? $subscription->payment_method : $subscription->get_payment_method())) {
            return $payment_method_to_display;
        }

        $payu_card_data = wpdesk_get_order_meta($subscription, '_payu_card_data', true);
        if (is_array($payu_card_data) && isset($payu_card_data['masked_card'])) {
            $payment_method_to_display = sprintf(
                __('PayU, karta: %s', 'woocommercee_payu'),
                $payu_card_data['masked_card']
            );
        }

        //$payment_method_to_display = 'GRO PayU';

        return $payment_method_to_display;
    }


    /**
     * scheduled_subscription_payment function.
     *
     * @param $amount_to_charge float The amount to charge.
     * @param $renewal_order    WC_Order A WC_Order object created to record the renewal payment.
     */
    public function scheduled_subscription_payment($amount_to_charge, $renewal_order)
    {

        if (isset($this->settings['api_version']) && $this->settings['api_version'] == 'rest_api') {
            try {
                $this->process_subscription_payment($renewal_order, $amount_to_charge);
                //$renewal_order->payment_complete();
            } catch (Exception $e) {
                $this->process_failed_payment($renewal_order, sprintf(__('Transakcja PayU zakończyła się niepowodzeniem (%s)', 'woocommerce_payu'), $e->getMessage()));
                //$this->notify_wcs_about_failed_payment( $renewal_order );
                $renewal_order->save();
            }
        } else {
            $this->process_failed_payment($renewal_order, __('Transakcja PayU zakończyła się niepowodzeniem (włącz REST API w ustawieniach PayU)', 'woocommerce_payu'));
        }
    }

    /**
     * Process order status from PayU
     *
     * @param WC_Order $order
     * @param string   $payu_status
     */
    protected function process_order_status(WC_Order $order, $payu_status, $is_trial = false)
    {

        if($order instanceof WC_Subscription && $is_trial === true) {
            $last_order = $order->get_last_order('all');
            if($last_order instanceof WC_Order && in_array($last_order->get_status(), ['pending', 'failed']) ) {
                if ($payu_status === 'COMPLETED') {
                    $order->add_order_note(
                        __(
                            'Płatność PayU została potwierdzona.',
                            'woocommerce_payu'
                        )
                    );
                    try {
                        $this->process_refund($order->get_id(), 1,  __('Zwrot za płatność testową karty', 'woocommerce_payu'));
                        $last_order = $order->get_last_order('all');
                        $this->scheduled_subscription_payment($last_order->get_total(), $last_order);
                    } catch (\Exception $e) {
                        $order->add_order_note(__('Wystąpił problem z realizacją zwrotu za testową płatność. Komunikat błędu: ' . $e->getMessage(), 'woocommerce_payu'));
                        $order->payment_failed();
                    }   
                } elseif ($payu_status === 'CANCELED') {
                    $order->add_order_note(
                        __(
                            'Płatność PayU została anulowana.',
                            'woocommerce_payu'
                        )
                    );
                    $order->payment_failed();
                } elseif ($payu_status === 'REJECTED') {
                    $order->add_order_note(
                        __(
                            'Płatność PayU została odrzucona.',
                            'woocommerce_payu'
                        )
                    );
                    $order->payment_failed();
                }
            }
            $order->update_status('active');         
        }else{
            if (in_array($order->get_status(), array('pending', 'failed'))) {
                if ($payu_status === 'COMPLETED') {
                    $order->add_order_note(
                        __(
                            'Płatność PayU została potwierdzona.',
                            'woocommerce_payu'
                        )
                    );
                    $order->payment_complete();
                    if ($is_trial === true) {
                        try {
                            $this->process_refund($order->get_id(), 1,  __('Zwrot za płatność testową karty', 'woocommerce_payu'));
                        } catch (\Exception $e) {
                            $order->add_order_note(__('Wystąpił problem z realizacją zwrotu za testową płatność. Komunikat błędu: ' . $e->getMessage(), 'woocommerce_payu'));
                        }                
                    }else{
                        $this->notify_wcs_about_completed_payment($order);    
                    }
                
                } elseif ($payu_status === 'CANCELED') {
                    //$this->process_failed_payment( $order, __( 'Anulowana płatność PayU.', 'woocommerce_payu' ) );
                    $this->notify_wcs_about_failed_payment($order);
                } elseif ($payu_status === 'REJECTED') {
                    //$this->process_failed_payment( $order, __( 'Odrzucona płatność PayU.', 'woocommerce_payu' ) );
                    $this->notify_wcs_about_failed_payment($order);
                }
            }
            $order->save();

        }
    }

    /**
     * Change order status using error code
     *
     * @param WC_Order   $order
     * @param string|int $error_id
     */
    protected function update_order_error_status(WC_Order $order, $error_id)
    {
        $statusData = $this->get_order_error_status($error_id);

        if (!empty($statusData['tstatus'])) {
            if ('failed' == $statusData['tstatus']) {
                $this->process_failed_payment($order);
            } else {
                $order->update_status($statusData['tstatus']);
            }
            $order->save();
        }
        if (!empty($statusData['tmsg'])) {
            $order->add_order_note($statusData['tmsg']);
            wc_add_notice($statusData['tmsg'], 'error');
        } else {
            $order->add_order_note(
                __(
                    'Płatności PayU: błąd',
                    'woocommerce_payu'
                ) . ' ' . $_GET['errorId']
            );
        }
    }

    /**
     *
     * @param  WC_Order $order
     * @param  string   $status
     * @return void
     * @throws Exception
     */
    public function update_order_status(\WC_Order $order,  $status)
    {
        switch ($status) {
        case 1:
        case 4:
            $order->update_status('pending');
            break;
        case 2:
        case 3:
        case 7:
            $this->process_failed_payment($order);
            $order->save();
            break;

        case 5:
            $order->update_status('processing');
            break;

        case 99:
            if (wpdesk_get_order_meta($order, '_payu_payment_completed', true) == '') {
                $order->add_order_note(__('Płatność PayU zatwierdzona.', 'woocommerce_payu'));
                wpdesk_update_order_meta($order, '_payu_payment_completed', 1);
                $order->payment_complete();
            }
            break;

        case 888:
            $order->update_status('on-hold');
            break;
        }
    }

    /**
     *
     * @param  WC_Order $order
     * @param  string   $message
     * @return void
     */
    private function process_failed_payment(WC_Order $order, $message = '', $status = 'failed')
    {
        if (!$this->is_failed) {
            empty($message) ? $order->update_status($status) : $order->update_status($status, $message);
            $this->is_failed = true;
        }
    }

    /**
     *
     * @param  WC_Order $order
     * @return void
     * @throws Exception
     */
    private function notify_wcs_about_failed_payment(WC_Order $order)
    {
        $subscriptions = array();
        if (wcs_order_contains_subscription($order)) {
            $subscriptions = wcs_get_subscriptions_for_order($order);
        } elseif (wcs_order_contains_renewal($order)) {
            $subscriptions = wcs_get_subscriptions_for_renewal_order($order);
        }

        if (!empty($subscriptions)) {
            foreach ($subscriptions as $subscription) {
                $subscription->payment_failed();
            }
        }
    }

    /**
     *
     * @param  WC_Order $order
     * @return void
     * @throws Exception
     */
    private function notify_wcs_about_completed_payment(WC_Order $order)
    {
        $subscriptions = array();
        if (wcs_order_contains_subscription($order)) {
            $subscriptions = wcs_get_subscriptions_for_order($order);
        } elseif (wcs_order_contains_renewal($order)) {
            $subscriptions = wcs_get_subscriptions_for_renewal_order($order);
        }

        if (!empty($subscriptions)) {
            foreach ($subscriptions as $subscription) {
                $subscription->payment_complete_for_order($order);
                $last_retry = WCS_Retry_Manager::store()->get_last_retry_for_order(wcs_get_objects_property($order, 'id'));
                if (is_object($last_retry)) {
                    $last_retry->update_status('complete');
                }
            }
        }
    }

    /**
     * @param WC_Order $order
     * @param int      $amount
     *
     * @throws Exception
     */
    public function process_subscription_payment(WC_Order $order, $amount = 0)
    {
        $payu_card_data = $payu_order = [];
        $recurring = self::RECURRING_PAYMENT_VALUE_FIRST;

        $renewal_id = wpdesk_get_order_meta($order, '_subscription_renewal', true);
        if (is_numeric($renewal_id)) {
            $subscription = wc_get_order($renewal_id);
            if (is_a($subscription, 'WC_Subscription')) {
                $payu_card_data = wpdesk_get_order_meta($subscription, '_payu_card_data', true);
                $payu_order     = wpdesk_get_order_meta($subscription, '_payu_order', true);
                if (empty($payu_order) && !empty(wpdesk_get_order_meta($order, '_payu_order', true))) {
                    if (0 === strpos($payu_card_data['value'], 'TOKC_')) {
                        $recurring = self::RECURRING_PAYMENT_VALUE_FOLLOW_UP;
                    }
                }
            }
        }

        if (empty($payu_card_data)) {
            $payu_card_data = wpdesk_get_order_meta($order, '_payu_card_data', true);
            $payu_order     = wpdesk_get_order_meta($order, '_payu_order', true);
        }

        if ('' === $payu_card_data) {
            throw new Exception(__('Brak danych do płatności: dane karty', 'woocommerce_payu'));
        }
        if (is_array($payu_order) && isset($payu_order['payMethods']['payMethod']) && isset($payu_order['payMethods']['payMethod']['value'])) {
            $payu_card_data['value'] = $payu_order['payMethods']['payMethod']['value'];
        }
        if (0 === strpos($payu_card_data['value'], 'TOKC_')) {
            $recurring = self::RECURRING_PAYMENT_VALUE_FOLLOW_UP;
        }
        wpdesk_update_order_meta($order, '_payu_card_data', $payu_card_data);
        $payu_order   = $this->create_payu_order($order, false, true, $payu_card_data, $recurring);

        $status_codes = [
        'WARNING_CONTINUE_3DS',
        'WARNING_CONTINUE_CVV',
        ];

        if (isset($payu_order['status']) && isset($payu_order['status']['statusCode']) && in_array(
            $payu_order['status']['statusCode'],
            $status_codes
        )
        ) {

            $this->process_failed_payment($order, __('Zamówienie nie zostało opłacone - wymagana dodatkowa autoryzacja karty.', 'woocommerce_payu'));

            $order->add_order_note(
                sprintf(
                    __(
                        'Zamówienie nie zostało opłacone - wymagana dodatkowa autoryzacja karty. Aby przeprowadzić autoryzację kliknij tutaj: %s.',
                        'woocommerce_payu'
                    ),
                    '<a href="' . $payu_order['redirectUri'] . '">' . $payu_order['redirectUri'] . '</a>'
                ), 1
            );

            if (!$this->wc_pre_30) {
                $order->save();
            }
        }

        $order_id = $this->wc_pre_30 ? $order->id : $order->get_id();

        // Also store it on the subscriptions being purchased or paid for in the order
        if (wcs_order_contains_subscription($order_id)) {
            $subscriptions = wcs_get_subscriptions_for_order($order_id);
        } elseif (wcs_order_contains_renewal($order_id)) {
            $subscriptions = wcs_get_subscriptions_for_renewal_order($order_id);
        } else {
            $subscriptions = [];
        }

        $payu_card_data = wpdesk_get_order_meta($order, '_payu_card_data', true);
        $payu_order     = wpdesk_get_order_meta($order, '_payu_order', true);

        foreach ($subscriptions as $subscription) {
            $subscription_id = $this->wc_pre_30 ? $subscription->id : $subscription->get_id();
            update_post_meta($subscription_id, '_payu_card_data', $payu_card_data);
            update_post_meta($subscription_id, '_payu_order', $payu_order);
        }
    }

    protected function is_subscription($order_id)
    {
        return (function_exists('wcs_order_contains_subscription') && (wcs_order_contains_subscription($order_id) || wcs_is_subscription($order_id) || wcs_order_contains_renewal($order_id)));
    }

    /**
     * Include the payment meta data required to process automatic recurring payments so that store managers can
     * manually set up automatic recurring payments for a customer via the Edit Subscriptions screen
     *
     * @param array           $payment_meta associative array of meta data required for automatic payments
     * @param WC_Subscription $subscription An instance of a subscription object
     *
     * @return array
     */
    public function add_subscription_payment_meta($payment_meta, $subscription)
    {
        $payment_meta[$this->id] = [
        'post_meta' => [
        '_payu_card_data' => [
        'value' => get_post_meta(
            ($this->wc_pre_30 ? $subscription->id : $subscription->get_id()),
            '_payu_card_data',
            true
        ),
        ],
        '_payu_order'     => [
                    'value' => get_post_meta(
                        ($this->wc_pre_30 ? $subscription->id : $subscription->get_id()),
                        '_payu_order',
                        true
                    ),
        ],
        ],
        ];

        return $payment_meta;
    }

    public function payment_fields()
    {
        global $wp;
        parent::payment_fields();
        $cart = WC()->cart;
        $cart->calculate_totals();
        $customer                   = WC()->customer;
        $checkout                   = WC()->checkout();
        $is_cart_contains_subscription = $this->is_cart_contains_subscription();
        $subscription_renewal       = false;
        if (!empty($wp->query_vars['order-pay'])) {
            $order = wc_get_order($wp->query_vars['order-pay']);
            if (get_class($order) == 'WC_Subscription') {
                $subscription_renewal = true;
            }
        }

        $currency_code     = get_woocommerce_currency();
        $pos = $this->payu_settings->get_pos_from_currency($currency_code);

        $widget_url      = 'https://secure.payu.com/front/widget/js/payu-bootstrap.js';
        $merchant_pos_id = $pos->get_pos_id();
        $key_2           = $pos->get_md5_key_2();
        if ($this->payu_settings->is_sandbox()) {
            $widget_url      = 'https://secure.snd.payu.com/front/widget/js/payu-bootstrap.js';
        }
        $shop_name         = $this->get_blog_alnum_name();
        $total_amount        = (floatval($cart->total) == 0) ? 1 : $cart->total;
        $total_amount      = round($total_amount, 2) * 100;
        $customer_language = 'pl';
        $store_card        = 'true';
        $recurring_payment = 'true';

        $customer_email = $checkout->get_value('billing_email');

        $sig_parameters = $currency_code . $customer_email . $customer_language . $merchant_pos_id . $recurring_payment . $shop_name . $store_card . $total_amount;

        $sig = hash('sha256', $sig_parameters . $key_2);

        include 'views/payu-subscription-form.php';
    } // End payment_fields()

    /**
     * Returns blog alphanumeric name from option.
     *
     * @return string
     */
    private function get_blog_alnum_name()
    {
        $blog_name = get_option('blogname', '');
        $blog_name = strval( preg_replace( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $blog_name ) );
        return trim($blog_name);
    }


    /**
     * Process the payment and return the result.
     *
     * @since 1.0.0
     */
    public function process_payment($order_id)
    {
        global $woocommerce;

        $ia = false;

        $order = wc_get_order($order_id);

        $subs           = false;
        $payu_card_data = [];

        // if (get_class($order) == 'WC_Subscription') {
        //     $redirect_url = $order->get_view_order_url();
        //     wpdesk_update_order_meta($order, '_payu_order', '');
        //     $this->save_payu_data($order);

        //     return [
        //         'result'   => 'success',
        //         'redirect' => $redirect_url
        //     ];
        // }

        if ($order->get_status() != 'pending') {
            $order->set_status('pending');
        }

        $this->save_payu_data($order);

        // if ($order->get_total() == 0) {
        //     $order->add_order_note(__(
        //         'Płatność PayU zatwierdzona - bezpłatny okres próbny.',
        //         'woocommerce_payu'
        //     ));
        //     wpdesk_update_order_meta($order, '_payu_payment_completed', 1);
        //     $order->payment_complete();
        //     //$order->update_status( 'completed' );
        //     if (!$this->wc_pre_30) {
        //         $order->save();
        //     }

        //     // Also store it on the subscriptions being purchased or paid for in the order
        //     if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order_id)) {
        //         $subscriptions = wcs_get_subscriptions_for_order($order_id);
        //     } elseif (function_exists('wcs_order_contains_renewal') && wcs_order_contains_renewal($order_id)) {
        //         $subscriptions = wcs_get_subscriptions_for_renewal_order($order_id);
        //     } else {
        //         $subscriptions = [];
        //     }

        //     $payu_card_data = wpdesk_get_order_meta($order, '_payu_card_data', true);

        //     foreach ($subscriptions as $subscription) {
        //         $subscription_id = $this->wc_pre_30 ? $subscription->id : $subscription->get_id();
        //         update_post_meta($subscription_id, '_payu_card_data', $payu_card_data);
        //     }

        //     $redirect_url = $order->get_checkout_order_received_url();

        //     return [
        //         'result'   => 'success',
        //         'redirect' => $redirect_url
        //     ];
        // }

        if ($order->get_status() != 'pending') {
            $order->set_status('pending');
        }

        if ($this->payu_settings->is_rest_api()) {
            try {
                $subs           = true;
                $payu_card_data = wpdesk_get_order_meta($order, '_payu_card_data', true);
                $recurring = self::RECURRING_PAYMENT_VALUE_FIRST;
                if (0 === strpos($payu_card_data['value'], 'TOKC_')) {
                    $recurring = self::RECURRING_PAYMENT_VALUE_FOLLOW_UP;
                }

                $payu_order = $this->create_payu_order($order, $ia, $subs, $payu_card_data, $recurring);
            } catch (Exception $e) {
                wc_add_notice($e->getMessage(), 'error');

                return [
                'result' => 'failure',
                ];
            }

            // Also store it on the subscriptions being purchased or paid for in the order
            if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order_id)) {
                $subscriptions = wcs_get_subscriptions_for_order($order_id);
            } elseif (function_exists('wcs_order_contains_renewal') && wcs_order_contains_renewal($order_id)) {
                $subscriptions = wcs_get_subscriptions_for_renewal_order($order_id);
            } else {
                $subscriptions = [];
            }

            $payu_card_data = wpdesk_get_order_meta($order, '_payu_card_data', true);
            $payu_order     = wpdesk_get_order_meta($order, '_payu_order', true);

            foreach ($subscriptions as $subscription) {
                $subscription_id = $this->wc_pre_30 ? $subscription->id : $subscription->get_id();
                update_post_meta($subscription_id, '_payu_card_data', $payu_card_data);
                update_post_meta($subscription_id, '_payu_order', $payu_order);
            }

            $redirect_url = $order->get_checkout_order_received_url();

            if (!empty($payu_order['redirectUri'])) {
                $redirect_url = $payu_order['redirectUri'];
            }

            return [
            'result'   => 'success',
            'redirect' => $redirect_url
            ];
        }
    } // End process_payment()

    /**
     * @return bool
     */
    public function is_cart_contains_subscription()
    {
        $cart = WC()->cart;
        if (!empty($cart)) {
            /**
       * @var WC_Product $item 
*/
            foreach ($cart->get_cart() as $cart_item) {
                $item = $cart_item['data'];
                if ($item->is_type('subscription')) {
                    return true;
                }
                if ($item->is_type('subscription_variation')) {
                    return true;
                }
                if (isset($cart_item['wcsatt_data']['active_subscription_scheme']) && !empty($cart_item['wcsatt_data']['active_subscription_scheme'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Is payment method available?
     *
     * @return bool
     */
    public function is_available()
    {
        if (is_admin()) {
            return true;
        }

        global $wp;
        $vars = $wp->query_vars;

        if (isset($vars['view-subscription']) && $this->is_subscription($vars['view-subscription'])) {
            return true;
        } elseif (isset($vars['order-pay']) && $this->is_subscription($vars['order-pay'])) {
            return true;
        } elseif (isset($_GET['change_payment_method']) && $this->is_subscription($_GET['change_payment_method'])) {
            return true;
        }

        return $this->is_cart_contains_subscription();
    }

    public static function get_retry_rules()
    {
        return array(
        array(
        'retry_after_interval'            => DAY_IN_SECONDS / 2, // how long to wait before retrying
        'email_template_customer'         => '', // don't bother the customer yet
        'email_template_admin'            => 'WCS_Email_Payment_Retry',
        'status_to_apply_to_order'        => 'pending',
        'status_to_apply_to_subscription' => 'active',
        ),
        array(
        'retry_after_interval'            => DAY_IN_SECONDS / 2,
        'email_template_customer'         => 'WCS_Email_Customer_Payment_Retry',
        'email_template_admin'            => 'WCS_Email_Payment_Retry',
        'status_to_apply_to_order'        => 'pending',
        'status_to_apply_to_subscription' => 'active',
        ),
        array(
        'retry_after_interval'            => DAY_IN_SECONDS,
        'email_template_customer'         => 'WCS_Email_Customer_Payment_Retry',
        'email_template_admin'            => 'WCS_Email_Payment_Retry',
        'status_to_apply_to_order'        => 'pending',
        'status_to_apply_to_subscription' => 'active',
        ),
        array(
        'retry_after_interval'            => DAY_IN_SECONDS * 2,
        'email_template_customer'         => 'WCS_Email_Customer_Payment_Retry',
        'email_template_admin'            => 'WCS_Email_Payment_Retry',
        'status_to_apply_to_order'        => 'pending',
        'status_to_apply_to_subscription' => 'active',
        ),
        array(
        'retry_after_interval'            => DAY_IN_SECONDS * 3,
        'email_template_customer'         => 'WCS_Email_Customer_Payment_Retry',
        'email_template_admin'            => 'WCS_Email_Payment_Retry',
        'status_to_apply_to_order'        => 'pending',
        'status_to_apply_to_subscription' => 'active',
        ),
        array(
        'retry_after_interval'            => DAY_IN_SECONDS * 7,
        'email_template_customer'         => 'WCS_Email_Customer_Payment_Retry',
        'email_template_admin'            => 'WCS_Email_Payment_Retry',
        'status_to_apply_to_order'        => 'pending',
        'status_to_apply_to_subscription' => 'active',
        ),
        );
    }

    public function get_subscription_from_order($order_id)
    {

        if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order_id)) {
            return wcs_get_subscriptions_for_order($order_id);
        } elseif (function_exists('wcs_order_contains_renewal') && wcs_order_contains_renewal($order_id)) {
            return wcs_get_subscriptions_for_renewal_order($order_id);
        }

        throw new \Exception('Subscription not exists for this order ID: ' . $order_id);
    }

    public static function is_order_subscription($order_id)
    {
        if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order_id)) {
            return true;
        } elseif (function_exists('wcs_order_contains_renewal') && wcs_order_contains_renewal($order_id)) {
            return true;
        }

        $order = wc_get_order($order_id);
        if(is_object($order) && $order instanceof WC_Subscription ) {
            return true;
        }

        return false;
    }
}
