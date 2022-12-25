<?php

class Przelewy24Generator
{
    const P24NOW = 266;
    /**
     * @var WC_Gateway_Przelewy24
     */
    private $gateway;

    /**
     * Przelewy24Generator constructor.
     *
     * @param WC_Gateway_Przelewy24 $gateway The class that provide configuration.
     */
    public function __construct(WC_Gateway_Przelewy24 $gateway) {
        $this->gateway = $gateway;
    }

    /**
     * Generate przelewy24 button link
     *
     * @param WC_Order $order
     * @param string|null $transaction_id
     * @return array|false
     * @throws Exception
     */
    public function generate_fields_array($order, $transaction_id = null)
    {
        if (!$order) return false;

        global $locale;

        $localization = !empty($locale) ? explode("_", $locale) : 'pl';

        /* We need raw order id integer to generate transaction and get metadata. */
        $order_id = (int) $order->get_id();

        if (is_null($transaction_id)) {
            $transaction_id = $order_id . "_" . uniqid(md5($order_id . '_' . date("ymds")), true);
        }

        $description_order_id = $order->get_order_number();
        // modifies order number if Sequential Order Numbers Pro plugin is installed
        if (class_exists('WC_Seq_Order_Number_Pro')) {
            $seq = new WC_Seq_Order_Number_Pro();
            $description_order_id = $seq->get_order_number($description_order_id, $order);
        } else if (class_exists('WC_Seq_Order_Number')) {
            $seq = new WC_Seq_Order_Number();
            $description_order_id = $seq->get_order_number($description_order_id, $order);
        }

        $config = $this->gateway->load_settings_from_db_formatted( $order->get_currency() );
        $config->access_mode_to_strict();

        //p24_opis depend of test mode
        $desc = ($config->is_p24_operation_mode( 'sandbox' ) ? __('Transakcja testowa', 'przelewy24') . ', ' : '') .
            __('Zamówienie nr', 'przelewy24') . ': ' . $description_order_id . ', ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . ', ' . date('Ymdhi');

        $status_page = add_query_arg(array('wc-api' => 'WC_Gateway_Przelewy24'), home_url('/'));

        /*Form send to przelewy24*/

        $amount = $order->get_total() * 100;
        $amount = number_format($amount, 0, "", "");

        $currency = strtoupper($order->get_currency());
        $przelewy24_arg = array(
            'p24_session_id' => addslashes($transaction_id),
            'p24_merchant_id' => (int) $config->get_merchant_id(),
            'p24_pos_id' => (int) $config->get_shop_id(),
            'p24_email' => filter_var($order->get_billing_email(), FILTER_SANITIZE_EMAIL),
            'p24_amount' => (int)$amount,
            'p24_currency' => filter_var($currency, FILTER_SANITIZE_STRING),
            'p24_description' => addslashes($desc),
            'p24_language' => filter_var($localization[0], FILTER_SANITIZE_STRING),
            'p24_client' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'p24_address' => $order->get_billing_address_1(),
            'p24_city' => $order->get_billing_city(),
            'p24_zip' => $order->get_billing_postcode(),
            'p24_country' => $order->get_billing_country(),
            'p24_encoding' => 'UTF-8',
            'p24_url_status' => filter_var($status_page, FILTER_SANITIZE_URL),
            'p24_url_return' => filter_var(WC_Gateway_Przelewy24::getReturnUrlStatic($order), FILTER_SANITIZE_URL),
            'p24_api_version' => P24_VERSION,
            'p24_ecommerce' => 'woocommerce_' . WOOCOMMERCE_VERSION,
            'p24_ecommerce2' => '1.0.0',
            'p24_method' => (int)get_post_meta($order_id, 'p24_method', true),
            'p24_shipping' => number_format($order->get_shipping_total() * 100, 0, '', ''),
            'p24_wait_for_result' => (int) $config->get_p24_wait_for_result(),
            'p24_provided_blik_code' => $order->get_meta(P24_Extra_Gateway::BLIK_CODE_META_KEY),
            'p24_channel' => '',
        );

        if ($this->activateChannel2ki($przelewy24_arg)) {
            $przelewy24_arg['p24_channel'] = 2048;
        }

        $productsInfo = array();
        foreach ($order->get_items() as $product) {
            $productsInfo[] = array(
                'name' => filter_var($product['name'], FILTER_SANITIZE_STRING),
                'description' => strip_tags(get_post($product['product_id'])->post_content),
                'quantity' => (int)$product['qty'],
                'price' => ($product['line_total'] / $product['qty']) * 100,
                'number' => (int)$product['product_id'],
            );
        }

        $shipping = number_format($order->get_shipping_total() * 100, 0, '', '');
        $translations = array(
            'virtual_product_name' => __('Dodatkowe kwoty [VAT, rabaty]', 'przelewy24'),
            'cart_as_product' => __('Twoje zamówienie', 'przelewy24'),
        );
        $p24Product = new Przelewy24Product($translations);
        $p24ProductItems = $p24Product->prepareCartItems($amount, $productsInfo, $shipping);
        $przelewy24_arg = array_merge($przelewy24_arg, $p24ProductItems);

        Przelewy24Class::checkMandatoryFieldsForAction($przelewy24_arg);

        return $przelewy24_arg;
    }

    /**
     * Generate payload for REST transaction.
     *
     * @param WC_Order $order
     * @param null|string $transaction_id
     * @param null|int $method
     * @return array
     * @throws Exception
     */
    public function generate_payload_for_rest( $order, $transaction_id = null, $method = null ) {
        $args = $this->generate_fields_array( $order, $transaction_id );
        $status_page = add_query_arg(array('wc-api' => 'WC_Gateway_Przelewy24', 'status' => 'REST'), home_url('/'));

        return array(
            "merchantId" => (int)$args['p24_merchant_id'],
            "posId" => (int)$args['p24_pos_id'],
            "sessionId" => (string)$args["p24_session_id"],
            "amount" => (int)$args["p24_amount"],
            "currency" => (string)$args["p24_currency"],
            "description" => (string)$args["p24_description"],
            "email" => (string)$args["p24_email"],
            "client" => (string)$args["p24_client"],
            "address" => (string)$args["p24_address"],
            "zip" => (string)$args["p24_zip"],
            "city" => (string)$args["p24_city"],
            "country" => (string)$args["p24_country"],
            "language" => (string)$args["p24_language"],
            "urlReturn" => (string)$args["p24_url_return"],
            "urlStatus" => (string)filter_var($status_page, FILTER_SANITIZE_URL),
            "shipping" => (int)$args["p24_shipping"],
            "encoding" => (string)$args["p24_encoding"],
            "method" => ($method||$args["p24_method"])?($method?:$args["p24_method"]):null
        );
    }

    /**
     * @param WC_Order $order
     * @param bool $autoSubmit
     * @param bool $makeRecurringForm
     * @return string
     * @throws Exception
     */
    public function generate_przelewy24_form($order, $autoSubmit = true, $makeRecurringForm = false)
    {
        $przelewy24_arg = $this->generate_fields_array($order);
        $config = $this->gateway->load_settings_from_db_formatted( $order->get_currency() );
        $strict_config = clone $config;
        $strict_config->access_mode_to_strict();

        $blik_code = '';
        if ($strict_config->get_p24_payinshop()) {
            $blik_code .= '<input type="hidden" name="p24_url_return" value="' . $przelewy24_arg['p24_url_return'] . '" />'."\n";
        }
        if ((int)$przelewy24_arg['p24_method'] === (int)P24_Extra_Gateway::BLIK_METHOD && $przelewy24_arg['p24_provided_blik_code']) {
            $blik_code .= '<input type="hidden" name="p24_provided_blik_code" value="' . $przelewy24_arg['p24_provided_blik_code'] . '" />'."\n";
        }

        $accept_in_shop = '';
        if ( $config->get_p24_acceptinshop() === 'yes' && $config->get_p24_show_methods_confirmation() === 'yes' ) {
            $accept_in_shop = '<p><label><input type="checkbox" required="required" name="p24_regulation_accept" value="1" />'. __('Tak, przeczytałem i akceptuję regulamin Przelewy24.', 'przelewy24') .'</label></p>';
        }

        $return = '<div id="payment" style="background: none"> ' .
            '<form action="'.get_rest_url(null,'/przelewy24/v1/create-payment').'" method="post" id="przelewy_payment_form"'.
            ($autoSubmit ? '' : ' onSubmit="return p24_processPayment()" ') .
            '><input type="hidden" name="order_id" value="'.((int) $order->get_id()).'"> ' .
            "<input type=\"hidden\" name=\"p24_session_id\" value=\"{$przelewy24_arg['p24_session_id']}\" />".
            "<input type=\"hidden\" name=\"p24_method\" />".
            $blik_code .
            $accept_in_shop .
            '<input type="submit" class="button alt" id="place_order" value="' . __('Potwierdzam zamówienie', 'przelewy24') . '" /> ' .
            '<p style="text-align:right; float:right; width:100%; font-size:12px;">' . __('Złożenie zamówienia wiąże się z obowiązkiem zapłaty', 'przelewy24') . '</p>' .
            '<a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Anuluj zamówienie', 'przelewy24') . '</a>' .
            ($autoSubmit ?
                '<script type="text/javascript">jQuery(function(){jQuery("body").block({message: "' .
                __('Dziękujemy za złożenie zamówienia. Za chwilę nastąpi przekierowanie na stronę przelewy24.pl', 'przelewy24') .
                '",overlayCSS: {background: "#fff",opacity: 0.6},css: {padding:20,textAlign:"center",color:"#555",border:"2px solid #AF2325",backgroundColor:"#fff",cursor:"wait",lineHeight:"32px"}});' .
                'jQuery("#przelewy_payment_form input[name=p24_regulation_accept]").prop("required", false);' .
                'p24_processPayment(); });' .
                '</script>' : '') .
            '</form>' .
            '</div>' .
            '';
        if (!!$makeRecurringForm) {
            $return .= <<<FORMRECURING
                        <form method="post" id="przelewy24FormRecuring" name="przelewy24FormRecuring" accept-charset="utf-8">
                            <input type="hidden" name="p24_session_id" value="{$przelewy24_arg['p24_session_id']}" />
                            <input type="hidden" name="p24_regulation_accept" />
                            <input type="hidden" name="p24_cc" />
                        </form>
FORMRECURING;
        }

        return $return;
    }

    /**
     * Check if activate channel 2024.
     *
     * @param array $przelewy24_arg
     *
     * @return bool
     */
    private function activateChannel2ki(array $przelewy24_arg)
    {
        $hasP24NowSelected = self::P24NOW === $przelewy24_arg['p24_method'];
        /* The p24_amount is PLN * 100. */
        $isAmount = (int)$przelewy24_arg['p24_amount'] < (100 * 10000) && (int)$przelewy24_arg['p24_amount'] > 0;
        $isCurrency = $przelewy24_arg['p24_currency'] == "PLN";

        return $isCurrency && $isAmount && $hasP24NowSelected;
    }
}
