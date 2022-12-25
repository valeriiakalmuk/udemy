<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Order\Compatibility;

use WGPayuVendor\WPDesk\WC\Helper\Compatibility\Traits\PostMetaDataManagement;
use WGPayuVendor\WPDesk\WC\Helper\Order\OrderCompatible;
class LegacyV27 extends \WGPayuVendor\WPDesk\WC\Helper\Order\Compatibility\AbstractWrapper implements \WGPayuVendor\WPDesk\WC\Helper\Order\OrderCompatible
{
    use PostMetaDataManagement;
    const META_NAME_BILLING_FIRST_NAME = '_billing_first_name';
    const META_NAME_BILLING_LAST_NAME = '_billing_last_name';
    const META_NAME_BILLING_COMPANY = '_billing_company';
    const META_NAME_BILLING_ADDRESS_1 = '_billing_address_1';
    const META_NAME_BILLING_ADDRESS_2 = '_billing_address_2';
    const META_NAME_BILLING_CITY = '_billing_city';
    const META_NAME_BILLING_STATE = '_billing_state';
    const META_NAME_BILLING_POSTCODE = '_billing_postcode';
    const META_NAME_BILLING_COUNTRY = '_billing_country';
    const META_NAME_BILLING_EMAIL = '_billing_email';
    const META_NAME_BILLING_PHONE = '_billing_phone';
    const META_NAME_SHIPPING_FIRST_NAME = '_shipping_first_name';
    const META_NAME_SHIPPING_LAST_NAME = '_shipping_last_name';
    const META_NAME_SHIPPING_COMPANY = '_shipping_company';
    const META_NAME_SHIPPING_ADDRESS_1 = '_shipping_address_1';
    const META_NAME_SHIPPING_ADDRESS_2 = '_shipping_address_2';
    const META_NAME_SHIPPING_CITY = '_shipping_city';
    const META_NAME_SHIPPING_STATE = '_shipping_state';
    const META_NAME_SHIPPING_POSTCODE = '_shipping_postcode';
    const META_NAME_SHIPPING_COUNTRY = '_shipping_country';
    const META_NAME_PAYMENT_METHOD = '_payment_method';
    const META_NAME_PAYMENT_METHOD_TITLE = '_payment_method_title';
    const META_NAME_TRANSACTION_ID = '_transaction_id';
    const META_NAME_CUSTOMER_IP_ADDRESS = '_customer_ip_address';
    const META_NAME_CUSTOMER_USER_AGENT = '_customer_user_agent';
    const META_NAME_CREATED_VIA = '_created_via';
    const META_NAME_CUSTOMER_NOTE = '_customer_note';
    const META_NAME_DATE_COMPLETED = '_date_completed';
    const META_NAME_DATE_PAID = '_date_paid';
    const META_NAME_CART_HASH = '_cart_hash';
    const META_NAME_ORDER_KEY = '_order_key';
    const META_NAME_CUSTOMER_ID = '_customer_id';
    const META_NAME_USER_ID = '_user_id';
    const META_NAME_PARENT_ID = '_parent_id';
    const META_NAME_STATUS = '_status';
    const META_NAME_VERSION = '_version';
    const META_NAME_PRICES_INCLUDE_TAX = '_prices_include_tax';
    const META_NAME_DISCOUNT_TOTAL = '_discount_total';
    const META_NAME_DISCOUNT_TAX = '_discount_tax';
    const META_NAME_SHIPPING_TOTAL = '_shipping_total';
    const META_NAME_SHIPPING_TAX = '_shipping_tax';
    const META_NAME_CART_TAX = '_cart_tax';
    const META_NAME_TOTAL = '_total';
    const META_NAME_ORDER_CURRENCY = '_order_currency';
    /** @var int */
    private $post_id;
    /** @var array */
    private $meta_data = [];
    public function __construct(\WC_Order $order)
    {
        parent::__construct($order);
        $this->post_id = static::get_order_id($order);
    }
    /**
     * Save data to the database.
     *
     * @return int order ID
     */
    public function save()
    {
        foreach ($this->meta_data as $name => $value) {
            \update_post_meta($this->post_id, $name, $value);
        }
        return $this->post_id;
    }
    public function set_status($new_status, $note = '', $manual_update = \false)
    {
        //return $this->order->set_status($new_status, $note, $manual_update);
    }
    public function get_customer_id($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_CUSTOMER_ID, \true);
    }
    public function get_user_id($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_USER_ID, \true);
    }
    public function get_billing_first_name($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_FIRST_NAME, \true);
    }
    public function get_billing_last_name($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_LAST_NAME, \true);
    }
    public function get_billing_company($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_COMPANY, \true);
    }
    public function get_billing_address_1($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_ADDRESS_1, \true);
    }
    public function get_billing_address_2($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_ADDRESS_2, \true);
    }
    public function get_billing_city($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_CITY, \true);
    }
    public function get_billing_state($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_STATE, \true);
    }
    public function get_billing_postcode($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_POSTCODE, \true);
    }
    public function get_billing_country($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_COUNTRY, \true);
    }
    public function get_billing_email($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_EMAIL, \true);
    }
    public function get_billing_phone($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_BILLING_PHONE, \true);
    }
    public function get_shipping_first_name($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_FIRST_NAME, \true);
    }
    public function get_shipping_last_name($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_LAST_NAME, \true);
    }
    public function get_shipping_company($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_COMPANY, \true);
    }
    public function get_shipping_address_1($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_ADDRESS_1, \true);
    }
    public function get_shipping_address_2($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_ADDRESS_2, \true);
    }
    public function get_shipping_city($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_CITY, \true);
    }
    public function get_shipping_state($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_STATE, \true);
    }
    public function get_shipping_postcode($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_POSTCODE, \true);
    }
    public function get_shipping_country($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_COUNTRY, \true);
    }
    public function get_payment_method($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_PAYMENT_METHOD, \true);
    }
    public function get_payment_method_title($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_PAYMENT_METHOD_TITLE, \true);
    }
    public function get_transaction_id($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_TRANSACTION_ID, \true);
    }
    public function get_customer_ip_address($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_CUSTOMER_IP_ADDRESS, \true);
    }
    public function get_customer_user_agent($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_CUSTOMER_USER_AGENT, \true);
    }
    public function get_created_via($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_CREATED_VIA, \true);
    }
    public function get_customer_note($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_CUSTOMER_NOTE, \true);
    }
    public function get_date_completed($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_DATE_COMPLETED, \true);
    }
    public function get_date_paid($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_DATE_PAID, \true);
    }
    public function get_formatted_billing_address($empty_content = '')
    {
        $address = \apply_filters('woocommerce_order_formatted_billing_address', $this->get_address('billing'), $this);
        $address = \WC()->countries->get_formatted_address($address);
        return $address ? $address : $empty_content;
    }
    public function get_formatted_shipping_address($empty_content = '')
    {
        $address = '';
        if ($this->has_shipping_address()) {
            $address = \apply_filters('woocommerce_order_formatted_shipping_address', $this->get_address('shipping'), $this);
            $address = \WC()->countries->get_formatted_address($address);
        }
        return $address ? $address : $empty_content;
    }
    public function has_shipping_address()
    {
        return !empty($this->get_address('shipping'));
    }
    public function has_billing_address()
    {
        return !empty($this->get_address('billing'));
    }
    public function set_order_key($value)
    {
        return $this->set_post_meta(self::META_NAME_ORDER_KEY, $value);
    }
    public function set_customer_id($value)
    {
        return $this->set_post_meta(self::META_NAME_CUSTOMER_ID, $value);
    }
    public function set_billing_first_name($value)
    {
        $this->set_post_meta(self::META_NAME_BILLING_FIRST_NAME, $value);
    }
    public function set_billing_last_name($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_LAST_NAME, $value);
    }
    public function set_billing_company($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_COMPANY, $value);
    }
    public function set_billing_address_1($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_ADDRESS_1, $value);
    }
    public function set_billing_address_2($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_ADDRESS_2, $value);
    }
    public function set_billing_city($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_CITY, $value);
    }
    public function set_billing_state($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_STATE, $value);
    }
    public function set_billing_postcode($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_POSTCODE, $value);
    }
    public function set_billing_country($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_COUNTRY, $value);
    }
    public function set_billing_email($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_EMAIL, $value);
    }
    public function set_billing_phone($value)
    {
        return $this->set_post_meta(self::META_NAME_BILLING_PHONE, $value);
    }
    public function set_shipping_first_name($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_FIRST_NAME, $value);
    }
    public function set_shipping_last_name($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_LAST_NAME, $value);
    }
    public function set_shipping_company($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_COMPANY, $value);
    }
    public function set_shipping_address_1($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_ADDRESS_1, $value);
    }
    public function set_shipping_address_2($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_ADDRESS_2, $value);
    }
    public function set_shipping_city($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_CITY, $value);
    }
    public function set_shipping_state($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_STATE, $value);
    }
    public function set_shipping_postcode($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_POSTCODE, $value);
    }
    public function set_shipping_country($value)
    {
        return $this->set_post_meta(self::META_NAME_SHIPPING_COUNTRY, $value);
    }
    public function set_payment_method($payment_method = '')
    {
        return $this->set_post_meta(self::META_NAME_PAYMENT_METHOD, $payment_method);
    }
    public function set_payment_method_title($value)
    {
        return $this->set_post_meta(self::META_NAME_PAYMENT_METHOD_TITLE, $value);
    }
    public function set_transaction_id($value)
    {
        return $this->set_post_meta(self::META_NAME_TRANSACTION_ID, $value);
    }
    public function set_customer_ip_address($value)
    {
        return $this->set_post_meta(self::META_NAME_CUSTOMER_IP_ADDRESS, $value);
    }
    public function set_customer_user_agent($value)
    {
        return $this->set_post_meta(self::META_NAME_CUSTOMER_USER_AGENT, $value);
    }
    public function set_created_via($value)
    {
        return $this->set_post_meta(self::META_NAME_CREATED_VIA, $value);
    }
    public function set_customer_note($value)
    {
        return $this->set_post_meta(self::META_NAME_CUSTOMER_NOTE, $value);
    }
    public function set_date_completed($date = null)
    {
        return $this->set_post_meta(self::META_NAME_DATE_COMPLETED, $date);
    }
    public function set_date_paid($date = null)
    {
        return $this->set_post_meta(self::META_NAME_DATE_PAID, $date);
    }
    public function set_cart_hash($value)
    {
        return $this->set_post_meta(self::META_NAME_CART_HASH, $value);
    }
    public function has_cart_hash($cart_hash = '')
    {
        return !\is_null($this->get_cart_hash());
    }
    public function get_cart_hash($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_CART_HASH, \true);
    }
    public function get_downloadable_items()
    {
        $downloads = array();
        foreach ($this->order->get_items() as $item) {
            if ($item['type'] === 'line_item') {
                if (empty($item['item_meta'])) {
                    continue;
                }
                $product = \wc_get_product($item['item_meta']);
                $item_downloads = $product->get_downloads();
                if ($product && $item_downloads) {
                    foreach ($item_downloads as $file) {
                        $downloads[] = array(
                            'download_url' => $file['download_url'],
                            'download_id' => $file['id'],
                            'product_id' => $product->id,
                            'product_name' => $product->get_name(),
                            'product_url' => $product->is_visible() ? $product->get_permalink() : '',
                            // Since 3.3.0.
                            'download_name' => $file['name'],
                            'order_id' => $this->get_id(),
                            'order_key' => $this->get_order_key(),
                            'downloads_remaining' => $file['downloads_remaining'],
                            'access_expires' => $file['access_expires'],
                        );
                    }
                }
            }
        }
        return \apply_filters('woocommerce_order_get_downloadable_items', $downloads, $this);
    }
    public function get_id()
    {
        return parent::get_order_id($this->order);
    }
    public function get_order_key($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_ORDER_KEY, \true);
    }
    public function needs_processing()
    {
        $needs_processing = \false;
        if (\count($this->order->get_items()) > 0) {
            foreach ($this->order->get_items() as $item) {
                if ($item['type'] === 'line_item') {
                    if (empty($item['item_meta'])) {
                        continue;
                    }
                    $product = \wc_get_product($item['item_meta']);
                    $virtual_downloadable_item = $product->is_downloadable() && $product->is_virtual();
                    if (\apply_filters('woocommerce_order_item_needs_processing', !$virtual_downloadable_item, $product, $this->get_id())) {
                        $needs_processing = \true;
                        break;
                    }
                }
            }
        }
        return $needs_processing;
    }
    public function get_edit_order_url()
    {
        $this->order->get_view_order_url();
    }
    public function get_parent_id($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_PARENT_ID, \true);
    }
    public function get_status($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_STATUS, \true);
    }
    public function get_version($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_VERSION, \true);
    }
    public function get_prices_include_tax($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_PRICES_INCLUDE_TAX, \true);
    }
    public function get_date_modified($context = 'view')
    {
        return \date("Y-m-d H:i:s", $this->order->modified_date);
    }
    public function get_discount_total($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_DISCOUNT_TOTAL, \true);
    }
    public function get_discount_tax($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_DISCOUNT_TAX, \true);
    }
    public function get_shipping_total($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_TOTAL, \true);
    }
    public function get_shipping_tax($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_SHIPPING_TAX, \true);
    }
    public function get_cart_tax($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_CART_TAX, \true);
    }
    public function get_total($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_TOTAL, \true);
    }
    public function get_total_tax($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_DISCOUNT_TOTAL, \true);
    }
    public function get_currency($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_ORDER_CURRENCY, \true);
    }
    public function get_item_meta_data($item_id)
    {
        return $this->order->get_item_meta_array($item_id);
    }
    public function reduce_order_stock()
    {
        $this->order->reduce_order_stock();
    }
}
