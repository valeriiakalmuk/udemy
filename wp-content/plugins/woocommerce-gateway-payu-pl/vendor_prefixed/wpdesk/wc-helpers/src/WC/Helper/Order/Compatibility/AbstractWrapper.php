<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Order\Compatibility;

abstract class AbstractWrapper
{
    /** @var \WC_Order */
    protected $order;
    /**
     * AbstractWrapper constructor.
     *
     * @param \WC_Order $order
     */
    public function __construct(\WC_Order $order)
    {
        $this->order = $order;
    }
    /**
     * @param \WC_Order $order
     *
     * @return int
     */
    static function get_order_id(\WC_Order $order)
    {
        return \method_exists($order, 'get_id') ? $order->get_id() : $order->id;
    }
    public function get_shipping_address_map_url()
    {
        return $this->order->get_shipping_address_map_url();
    }
    public function get_formatted_billing_full_name()
    {
        return $this->order->get_formatted_billing_full_name();
    }
    public function get_formatted_shipping_full_name()
    {
        return $this->order->get_formatted_shipping_full_name();
    }
    public function get_user()
    {
        return $this->order->get_user();
    }
    public function get_address($type = 'billing')
    {
        return $this->order->get_address($type);
    }
    public function is_editable()
    {
        return $this->order->is_editable();
    }
    public function is_paid()
    {
        return $this->order->is_paid();
    }
    public function is_download_permitted()
    {
        return $this->order->is_download_permitted();
    }
    public function needs_shipping_address()
    {
        return $this->order->needs_shipping_address();
    }
    public function has_downloadable_item()
    {
        return $this->order->has_downloadable_item();
    }
    public function get_checkout_payment_url($on_checkout = \false)
    {
        return $this->order->get_checkout_payment_url($on_checkout);
    }
    public function get_checkout_order_received_url()
    {
        return $this->order->get_checkout_order_received_url();
    }
    public function get_cancel_order_url($redirect = '')
    {
        return $this->order->get_cancel_order_url($redirect);
    }
    public function get_cancel_order_url_raw($redirect = '')
    {
        return $this->order->get_cancel_order_url_raw($redirect);
    }
    public function get_cancel_endpoint()
    {
        return $this->order->get_cancel_endpoint();
    }
    public function get_view_order_url()
    {
        return $this->order->get_view_order_url();
    }
    public function add_order_note($note, $is_customer_note = 0, $added_by_user = \false)
    {
        return $this->order->add_order_note($note, $is_customer_note, $added_by_user);
    }
    public function get_customer_order_notes()
    {
        return $this->order->get_customer_order_notes();
    }
    public function get_refunds()
    {
        return $this->order->get_refunds();
    }
    public function get_total_refunded()
    {
        return $this->order->get_total_refunded();
    }
    public function get_total_tax_refunded()
    {
        return $this->order->get_total_tax_refunded();
    }
    public function get_total_shipping_refunded()
    {
        return $this->order->get_total_shipping_refunded();
    }
    public function get_item_count_refunded($item_type = '')
    {
        return $this->order->get_item_count_refunded($item_type);
    }
    public function get_total_qty_refunded($item_type = 'line_item')
    {
        return $this->order->get_total_qty_refunded($item_type);
    }
    public function get_qty_refunded_for_item($item_id, $item_type = 'line_item')
    {
        return $this->order->get_qty_refunded_for_item($item_id, $item_type);
    }
    public function get_total_refunded_for_item($item_id, $item_type = 'line_item')
    {
        return $this->order->get_total_refunded_for_item($item_id, $item_type);
    }
    public function get_tax_refunded_for_item($item_id, $tax_id, $item_type = 'line_item')
    {
        return $this->order->get_tax_refunded_for_item($item_id, $tax_id, $item_type);
    }
    public function get_total_tax_refunded_by_rate_id($rate_id)
    {
        return $this->order->get_total_tax_refunded_by_rate_id($rate_id);
    }
    public function get_remaining_refund_amount()
    {
        return $this->order->get_remaining_refund_amount();
    }
    public function get_remaining_refund_items()
    {
        return $this->order->get_remaining_refund_items();
    }
    public function get_order_item_totals($tax_display = '')
    {
        return $this->order->get_order_item_totals($tax_display);
    }
    public function needs_payment()
    {
        return $this->order->needs_payment();
    }
    public function key_is_valid($key)
    {
        return $this->order->key_is_valid($key);
    }
    public function get_order_number()
    {
        return $this->order->get_order_number();
    }
    public function update_status($new_status, $note = '', $manual = \false)
    {
        return $this->order->update_status($new_status, $note, $manual);
    }
}
