<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Order\Compatibility;

use WGPayuVendor\WPDesk\WC\Helper\Order\OrderCompatible;
class LegacyV33 extends \WGPayuVendor\WPDesk\WC\Helper\Order\Compatibility\AbstractWrapper implements \WGPayuVendor\WPDesk\WC\Helper\Order\OrderCompatible
{
    public function save()
    {
        return $this->order->save();
    }
    public function set_status($new_status, $note = '', $manual_update = \false)
    {
        return $this->order->set_status($new_status, $note, $manual_update);
    }
    public function get_parent_id($context = 'view')
    {
        return $this->order->get_parent_id($context);
    }
    public function get_status($context = 'view')
    {
        return $this->order->get_status($context);
    }
    public function get_version($context = 'view')
    {
        return $this->order->get_version($context);
    }
    public function get_prices_include_tax($context = 'view')
    {
        return $this->order->get_prices_include_tax($context);
    }
    public function get_date_modified($context = 'view')
    {
        return $this->order->get_date_modified($context);
    }
    public function get_discount_total($context = 'view')
    {
        return $this->order->get_discount_total($context);
    }
    public function get_discount_tax($context = 'view')
    {
        return $this->order->get_discount_tax($context);
    }
    public function get_shipping_total($context = 'view')
    {
        return $this->order->get_shipping_total($context);
    }
    public function get_shipping_tax($context = 'view')
    {
        return $this->order->get_shipping_tax($context);
    }
    public function get_cart_tax($context = 'view')
    {
        return $this->order->get_cart_tax($context);
    }
    public function get_total($context = 'view')
    {
        return $this->order->get_total($context);
    }
    public function get_total_tax($context = 'view')
    {
        return $this->order->get_total_tax($context);
    }
    public function get_currency($context = 'view')
    {
        return $this->order->get_currency($context);
    }
    public function get_order_key($context = 'view')
    {
        return $this->order->get_order_key($context);
    }
    public function get_customer_id($context = 'view')
    {
        return $this->order->get_customer_id($context);
    }
    public function get_user_id($context = 'view')
    {
        return $this->order->get_user_id($context);
    }
    public function get_billing_first_name($context = 'view')
    {
        return $this->order->get_billing_first_name($context);
    }
    public function get_billing_last_name($context = 'view')
    {
        return $this->order->get_billing_last_name($context);
    }
    public function get_billing_company($context = 'view')
    {
        return $this->order->get_billing_company($context);
    }
    public function get_billing_address_1($context = 'view')
    {
        return $this->order->get_billing_address_1($context);
    }
    public function get_billing_address_2($context = 'view')
    {
        return $this->order->get_billing_address_2($context);
    }
    public function get_billing_city($context = 'view')
    {
        return $this->order->get_billing_city($context);
    }
    public function get_billing_state($context = 'view')
    {
        return $this->order->get_billing_state($context);
    }
    public function get_billing_postcode($context = 'view')
    {
        return $this->order->get_billing_postcode($context);
    }
    public function get_billing_country($context = 'view')
    {
        return $this->order->get_billing_country($context);
    }
    public function get_billing_email($context = 'view')
    {
        return $this->order->get_billing_email($context);
    }
    public function get_billing_phone($context = 'view')
    {
        return $this->order->get_billing_phone($context);
    }
    public function get_shipping_first_name($context = 'view')
    {
        return $this->order->get_shipping_first_name($context);
    }
    public function get_shipping_last_name($context = 'view')
    {
        return $this->order->get_shipping_last_name($context);
    }
    public function get_shipping_company($context = 'view')
    {
        return $this->order->get_shipping_company($context);
    }
    public function get_shipping_address_1($context = 'view')
    {
        return $this->order->get_shipping_address_1($context);
    }
    public function get_shipping_address_2($context = 'view')
    {
        return $this->order->get_shipping_address_2($context);
    }
    public function get_shipping_city($context = 'view')
    {
        return $this->order->get_shipping_city($context);
    }
    public function get_shipping_state($context = 'view')
    {
        return $this->order->get_shipping_state($context);
    }
    public function get_shipping_postcode($context = 'view')
    {
        return $this->order->get_shipping_postcode($context);
    }
    public function get_shipping_country($context = 'view')
    {
        return $this->order->get_shipping_country($context);
    }
    public function get_payment_method($context = 'view')
    {
        return $this->order->get_payment_method($context);
    }
    public function get_payment_method_title($context = 'view')
    {
        return $this->order->get_payment_method_title($context);
    }
    public function get_transaction_id($context = 'view')
    {
        return $this->order->get_transaction_id($context);
    }
    public function get_customer_ip_address($context = 'view')
    {
        return $this->order->get_customer_ip_address($context);
    }
    public function get_customer_user_agent($context = 'view')
    {
        return $this->order->get_customer_user_agent($context);
    }
    public function get_created_via($context = 'view')
    {
        return $this->order->get_created_via($context);
    }
    public function get_customer_note($context = 'view')
    {
        return $this->order->get_customer_note($context);
    }
    public function get_date_completed($context = 'view')
    {
        return $this->order->get_date_completed($context);
    }
    public function get_date_paid($context = 'view')
    {
        return $this->order->get_date_paid($context);
    }
    public function get_cart_hash($context = 'view')
    {
        return $this->order->get_cart_hash($context);
    }
    public function get_formatted_billing_address($empty_content = '')
    {
        return $this->order->get_formatted_billing_address($empty_content);
    }
    public function get_formatted_shipping_address($empty_content = '')
    {
        return $this->order->get_formatted_shipping_address($empty_content);
    }
    public function has_billing_address()
    {
        return $this->order->has_billing_address();
    }
    public function has_shipping_address()
    {
        return $this->order->has_shipping_address();
    }
    public function set_order_key($value)
    {
        return $this->order->set_order_key($value);
    }
    public function set_customer_id($value)
    {
        return $this->order->set_customer_id($value);
    }
    public function set_billing_first_name($value)
    {
        return $this->order->set_billing_first_name($value);
    }
    public function set_billing_last_name($value)
    {
        return $this->order->set_billing_last_name($value);
    }
    public function set_billing_company($value)
    {
        return $this->order->set_billing_company($value);
    }
    public function set_billing_address_1($value)
    {
        return $this->order->set_billing_address_1($value);
    }
    public function set_billing_address_2($value)
    {
        return $this->order->set_billing_address_2($value);
    }
    public function set_billing_city($value)
    {
        return $this->order->set_billing_city($value);
    }
    public function set_billing_state($value)
    {
        return $this->order->set_billing_state($value);
    }
    public function set_billing_postcode($value)
    {
        return $this->order->set_billing_postcode($value);
    }
    public function set_billing_country($value)
    {
        return $this->order->set_billing_country($value);
    }
    public function set_billing_email($value)
    {
        return $this->order->set_billing_email($value);
    }
    public function set_billing_phone($value)
    {
        return $this->order->set_billing_phone($value);
    }
    public function set_shipping_first_name($value)
    {
        return $this->order->set_shipping_first_name($value);
    }
    public function set_shipping_last_name($value)
    {
        return $this->order->set_shipping_last_name($value);
    }
    public function set_shipping_company($value)
    {
        return $this->order->set_shipping_company($value);
    }
    public function set_shipping_address_1($value)
    {
        return $this->order->set_shipping_address_1($value);
    }
    public function set_shipping_address_2($value)
    {
        return $this->order->set_shipping_address_2($value);
    }
    public function set_shipping_city($value)
    {
        return $this->order->set_shipping_city($value);
    }
    public function set_shipping_state($value)
    {
        return $this->order->set_shipping_state($value);
    }
    public function set_shipping_postcode($value)
    {
        return $this->order->set_shipping_postcode($value);
    }
    public function set_shipping_country($value)
    {
        return $this->order->set_shipping_country($value);
    }
    public function set_payment_method($payment_method = '')
    {
        return $this->order->set_payment_method($payment_method);
    }
    public function set_payment_method_title($value)
    {
        return $this->order->set_payment_method_title($value);
    }
    public function set_transaction_id($value)
    {
        return $this->order->set_transaction_id($value);
    }
    public function set_customer_ip_address($value)
    {
        return $this->order->set_customer_ip_address($value);
    }
    public function set_customer_user_agent($value)
    {
        return $this->order->set_customer_user_agent($value);
    }
    public function set_created_via($value)
    {
        return $this->order->set_created_via($value);
    }
    public function set_customer_note($value)
    {
        return $this->order->set_customer_note($value);
    }
    public function set_date_completed($date = null)
    {
        return $this->order->set_date_completed($date);
    }
    public function set_date_paid($date = null)
    {
        return $this->order->set_date_paid($date);
    }
    public function set_cart_hash($value)
    {
        return $this->order->set_cart_hash($value);
    }
    public function has_cart_hash($cart_hash = '')
    {
        return $this->order->has_cart_hash($cart_hash);
    }
    public function get_downloadable_items()
    {
        return $this->order->get_downloadable_items();
    }
    public function needs_processing()
    {
        return $this->order->needs_processing();
    }
    public function get_edit_order_url()
    {
        return $this->order->get_edit_order_url();
    }
    public function get_id()
    {
        return $this->order->get_id();
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
