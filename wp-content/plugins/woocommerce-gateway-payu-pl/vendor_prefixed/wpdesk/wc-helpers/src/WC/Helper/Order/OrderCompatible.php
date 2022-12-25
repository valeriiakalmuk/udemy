<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Order;

/**
 * Interface for Order as is in the latest WC 3.x version
 */
interface OrderCompatible
{
    /**
     * Save data to the database.
     *
     * @since 3.0.0
     * @return int order ID
     */
    public function save();
    /**
     * Set order status.
     *
     * @since 3.0.0
     *
     * @param string $new_status Status to change the order to. No internal wc- prefix is required.
     * @param string $note Optional note to add.
     * @param bool $manual_update Is this a manual order status change?.
     *
     * @return array
     */
    public function set_status($new_status, $note = '', $manual_update = \false);
    /**
     * Updates status of order immediately. Order must exist.
     *
     * @uses WPDeskHelpersWC_Order::set_status()
     *
     * @param string $new_status Status to change the order to. No internal wc- prefix is required.
     * @param string $note Optional note to add.
     * @param bool $manual Is this a manual order status change?.
     *
     * @return bool
     */
    public function update_status($new_status, $note = '', $manual = \false);
    /**
     * Get parent order ID.
     *
     * @since 3.0.0
     * @param  string $context View or edit context.
     * @return integer
     */
    public function get_parent_id($context = 'view');
    /**
     * Return the order statuses without wc- internal prefix.
     *
     * @param  string $context
     * @return string
     */
    public function get_status($context = 'view');
    /**
     * Get order_version.
     *
     * @param  string $context
     * @return string
     */
    public function get_version($context = 'view');
    /**
     * Get prices_include_tax.
     *
     * @param  string $context
     * @return bool
     */
    public function get_prices_include_tax($context = 'view');
    /**
     * Get date_modified.
     *
     * @param  string $context
     * @return \WC_DateTime|NULL object if the date is set or null if there is no date.
     */
    public function get_date_modified($context = 'view');
    /**
     * Get discount_total.
     *
     * @param  string $context
     * @return string
     */
    public function get_discount_total($context = 'view');
    /**
     * Get discount_tax.
     *
     * @param  string $context
     * @return string
     */
    public function get_discount_tax($context = 'view');
    /**
     * Get shipping_total.
     *
     * @param  string $context
     * @return string
     */
    public function get_shipping_total($context = 'view');
    /**
     * Get shipping_tax.
     *
     * @param  string $context
     * @return string
     */
    public function get_shipping_tax($context = 'view');
    /**
     * Gets cart tax amount.
     *
     * @param  string $context
     * @return float
     */
    public function get_cart_tax($context = 'view');
    /**
     * Gets order grand total. incl. taxes. Used in gateways.
     *
     * @param  string $context
     * @return float
     */
    public function get_total($context = 'view');
    /**
     * Get total tax amount. Alias for get_order_tax().
     *
     * @param  string $context
     * @return float
     */
    public function get_total_tax($context = 'view');
    /**
     * Gets order currency.
     *
     * @param  string $context
     * @return string
     */
    public function get_currency($context = 'view');
    /**
     * Gets the order number for display (by default, order ID).
     *
     * @return string
     */
    public function get_order_number();
    /**
     * Get order key.
     *
     * @since  3.0.0
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_order_key($context = 'view');
    /**
     * Get customer_id.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return int
     */
    public function get_customer_id($context = 'view');
    /**
     * Alias for get_customer_id().
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return int
     */
    public function get_user_id($context = 'view');
    /**
     * Get the user associated with the order. False for guests.
     *
     * @return \WP_User|false
     */
    public function get_user();
    /**
     * Get billing first name.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_first_name($context = 'view');
    /**
     * Get billing last name.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_last_name($context = 'view');
    /**
     * Get billing company.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_company($context = 'view');
    /**
     * Get billing address line 1.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_address_1($context = 'view');
    /**
     * Get billing address line 2.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_address_2($context = 'view');
    /**
     * Get billing city.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_city($context = 'view');
    /**
     * Get billing state.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_state($context = 'view');
    /**
     * Get billing postcode.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_postcode($context = 'view');
    /**
     * Get billing country.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_country($context = 'view');
    /**
     * Get billing email.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_email($context = 'view');
    /**
     * Get billing phone.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_billing_phone($context = 'view');
    /**
     * Get shipping first name.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_first_name($context = 'view');
    /**
     * Get shipping_last_name.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_last_name($context = 'view');
    /**
     * Get shipping company.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_company($context = 'view');
    /**
     * Get shipping address line 1.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_address_1($context = 'view');
    /**
     * Get shipping address line 2.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_address_2($context = 'view');
    /**
     * Get shipping city.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_city($context = 'view');
    /**
     * Get shipping state.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_state($context = 'view');
    /**
     * Get shipping postcode.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_postcode($context = 'view');
    /**
     * Get shipping country.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_shipping_country($context = 'view');
    /**
     * Get the payment method.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_payment_method($context = 'view');
    /**
     * Get payment method title.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_payment_method_title($context = 'view');
    /**
     * Get transaction d.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_transaction_id($context = 'view');
    /**
     * Get customer ip address.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_customer_ip_address($context = 'view');
    /**
     * Get customer user agent.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_customer_user_agent($context = 'view');
    /**
     * Get created via.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_created_via($context = 'view');
    /**
     * Get customer note.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_customer_note($context = 'view');
    /**
     * Get date completed.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return \WC_DateTime|NULL object if the date is set or null if there is no date.
     */
    public function get_date_completed($context = 'view');
    /**
     * Get date paid.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return \WC_DateTime|NULL object if the date is set or null if there is no date.
     */
    public function get_date_paid($context = 'view');
    /**
     * Get cart hash.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     *
     * @return string
     */
    public function get_cart_hash($context = 'view');
    /**
     * Returns the requested address in raw, non-formatted way.
     * Note: Merges raw data with get_prop data so changes are returned too.
     *
     * @since  2.4.0
     *
     * @param  string $type Billing or shipping. Anything else besides 'billing' will return shipping address.
     *
     * @return array The stored address after filter.
     */
    public function get_address($type = 'billing');
    /**
     * Get a formatted shipping address for the order.
     *
     * @return string
     */
    public function get_shipping_address_map_url();
    /**
     * Get a formatted billing full name.
     *
     * @return string
     */
    public function get_formatted_billing_full_name();
    /**
     * Get a formatted shipping full name.
     *
     * @return string
     */
    public function get_formatted_shipping_full_name();
    /**
     * Get a formatted billing address for the order.
     *
     * @param string $empty_content Content to show if no address is present. @since 3.3.0.
     *
     * @return string
     */
    public function get_formatted_billing_address($empty_content = '');
    /**
     * Get a formatted shipping address for the order.
     *
     * @param string $empty_content Content to show if no address is present. @since 3.3.0.
     *
     * @return string
     */
    public function get_formatted_shipping_address($empty_content = '');
    /**
     * Returns true if the order has a billing address.
     *
     * @since  3.0.4
     * @return boolean
     */
    public function has_billing_address();
    /**
     * Returns true if the order has a shipping address.
     *
     * @since  3.0.4
     * @return boolean
     */
    public function has_shipping_address();
    /**
     * Set order key.
     *
     * @param string $value Max length 22 chars.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_order_key($value);
    /**
     * Set customer id.
     *
     * @param int $value Customer ID.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_customer_id($value);
    /**
     * Set billing first name.
     *
     * @param string $value Billing first name.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_first_name($value);
    /**
     * Set billing last name.
     *
     * @param string $value Billing last name.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_last_name($value);
    /**
     * Set billing company.
     *
     * @param string $value Billing company.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_company($value);
    /**
     * Set billing address line 1.
     *
     * @param string $value Billing address line 1.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_address_1($value);
    /**
     * Set billing address line 2.
     *
     * @param string $value Billing address line 2.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_address_2($value);
    /**
     * Set billing city.
     *
     * @param string $value Billing city.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_city($value);
    /**
     * Set billing state.
     *
     * @param string $value Billing state.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_state($value);
    /**
     * Set billing postcode.
     *
     * @param string $value Billing postcode.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_postcode($value);
    /**
     * Set billing country.
     *
     * @param string $value Billing country.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_country($value);
    /**
     * Set billing email.
     *
     * @param string $value Billing email.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_email($value);
    /**
     * Set billing phone.
     *
     * @param string $value Billing phone.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_billing_phone($value);
    /**
     * Set shipping first name.
     *
     * @param string $value Shipping first name.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_first_name($value);
    /**
     * Set shipping last name.
     *
     * @param string $value Shipping last name.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_last_name($value);
    /**
     * Set shipping company.
     *
     * @param string $value Shipping company.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_company($value);
    /**
     * Set shipping address line 1.
     *
     * @param string $value Shipping address line 1.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_address_1($value);
    /**
     * Set shipping address line 2.
     *
     * @param string $value Shipping address line 2.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_address_2($value);
    /**
     * Set shipping city.
     *
     * @param string $value Shipping city.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_city($value);
    /**
     * Set shipping state.
     *
     * @param string $value Shipping state.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_state($value);
    /**
     * Set shipping postcode.
     *
     * @param string $value Shipping postcode.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_postcode($value);
    /**
     * Set shipping country.
     *
     * @param string $value Shipping country.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_shipping_country($value);
    /**
     * Set the payment method.
     *
     * @param string $payment_method Supports WC_Payment_Gateway for bw compatibility with < 3.0.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_payment_method($payment_method = '');
    /**
     * Set payment method title.
     *
     * @param string $value Payment method title.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_payment_method_title($value);
    /**
     * Set transaction id.
     *
     * @param string $value Transaction id.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_transaction_id($value);
    /**
     * Set customer ip address.
     *
     * @param string $value Customer ip address.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_customer_ip_address($value);
    /**
     * Set customer user agent.
     *
     * @param string $value Customer user agent.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_customer_user_agent($value);
    /**
     * Set created via.
     *
     * @param string $value Created via.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_created_via($value);
    /**
     * Set customer note.
     *
     * @param string $value Customer note.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_customer_note($value);
    /**
     * Set date completed.
     *
     * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if their is no date.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_date_completed($date = null);
    /**
     * Set date paid.
     *
     * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if their is no date.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_date_paid($date = null);
    /**
     * Set cart hash.
     *
     * @param string $value Cart hash.
     *
     * @throws \WC_Data_Exception Throws exception when invalid data is found.
     */
    public function set_cart_hash($value);
    /**
     * Check if an order key is valid.
     *
     * @param string $key Order key.
     *
     * @return bool
     */
    public function key_is_valid($key);
    /**
     * See if order matches cart_hash.
     *
     * @param string $cart_hash Cart hash.
     *
     * @return bool
     */
    public function has_cart_hash($cart_hash = '');
    /**
     * Checks if an order can be edited, specifically for use on the Edit Order screen.
     *
     * @return bool
     */
    public function is_editable();
    /**
     * Returns if an order has been paid for based on the order status.
     *
     * @since 2.5.0
     * @return bool
     */
    public function is_paid();
    /**
     * Checks if product download is permitted.
     *
     * @return bool
     */
    public function is_download_permitted();
    /**
     * Checks if an order needs display the shipping address, based on shipping method.
     *
     * @return bool
     */
    public function needs_shipping_address();
    /**
     * Returns true if the order contains a downloadable product.
     *
     * @return bool
     */
    public function has_downloadable_item();
    /**
     * Get downloads from all line items for this order.
     *
     * @since  3.2.0
     * @return array
     */
    public function get_downloadable_items();
    /**
     * Checks if an order needs payment, based on status and order total.
     *
     * @return bool
     */
    public function needs_payment();
    /**
     * See if the order needs processing before it can be completed.
     *
     * Orders which only contain virtual, downloadable items do not need admin
     * intervention.
     *
     * @since 3.0.0
     * @return bool
     */
    public function needs_processing();
    /**
     * Generates a URL so that a customer can pay for their (unpaid - pending) order. Pass 'true' for the checkout version which doesn't offer gateway choices.
     *
     * @param  bool $on_checkout If on checkout.
     *
     * @return string
     */
    public function get_checkout_payment_url($on_checkout = \false);
    /**
     * Generates a URL for the thanks page (order received).
     *
     * @return string
     */
    public function get_checkout_order_received_url();
    /**
     * Generates a URL so that a customer can cancel their (unpaid - pending) order.
     *
     * @param string $redirect Redirect URL.
     *
     * @return string
     */
    public function get_cancel_order_url($redirect = '');
    /**
     * Generates a raw (unescaped) cancel-order URL for use by payment gateways.
     *
     * @param string $redirect Redirect URL.
     *
     * @return string The unescaped cancel-order URL.
     */
    public function get_cancel_order_url_raw($redirect = '');
    /**
     * Helper method to return the cancel endpoint.
     *
     * @return string the cancel endpoint; either the cart page or the home page.
     */
    public function get_cancel_endpoint();
    /**
     * Generates a URL to view an order from the my account page.
     *
     * @return string
     */
    public function get_view_order_url();
    /**
     * Get's the URL to edit the order in the backend.
     *
     * @since 3.3.0
     * @return string
     */
    public function get_edit_order_url();
    /**
     * Adds a note (comment) to the order. Order must exist.
     *
     * @param  string $note Note to add.
     * @param  int $is_customer_note Is this a note for the customer?.
     * @param  bool $added_by_user Was the note added by a user?.
     *
     * @return int                       Comment ID.
     */
    public function add_order_note($note, $is_customer_note = 0, $added_by_user = \false);
    /**
     * List order notes (public) for the customer.
     *
     * @return array
     */
    public function get_customer_order_notes();
    /**
     * Get order refunds.
     *
     * @since 2.2
     * @return array of WC_Order_Refund objects
     */
    public function get_refunds();
    /**
     * Get amount already refunded.
     *
     * @since 2.2
     * @return string
     */
    public function get_total_refunded();
    /**
     * Get the total tax refunded.
     *
     * @since  2.3
     * @return float
     */
    public function get_total_tax_refunded();
    /**
     * Get the total shipping refunded.
     *
     * @since  2.4
     * @return float
     */
    public function get_total_shipping_refunded();
    /**
     * Gets the count of order items of a certain type that have been refunded.
     *
     * @since  2.4.0
     *
     * @param string $item_type Item type.
     *
     * @return string
     */
    public function get_item_count_refunded($item_type = '');
    /**
     * Get the total number of items refunded.
     *
     * @since  2.4.0
     *
     * @param  string $item_type Type of the item we're checking, if not a line_item.
     *
     * @return int
     */
    public function get_total_qty_refunded($item_type = 'line_item');
    /**
     * Get the refunded amount for a line item.
     *
     * @param  int $item_id ID of the item we're checking.
     * @param  string $item_type Type of the item we're checking, if not a line_item.
     *
     * @return int
     */
    public function get_qty_refunded_for_item($item_id, $item_type = 'line_item');
    /**
     * Get the refunded amount for a line item.
     *
     * @param  int $item_id ID of the item we're checking.
     * @param  string $item_type Type of the item we're checking, if not a line_item.
     *
     * @return int
     */
    public function get_total_refunded_for_item($item_id, $item_type = 'line_item');
    /**
     * Get the refunded tax amount for a line item.
     *
     * @param  int $item_id ID of the item we're checking.
     * @param  int $tax_id ID of the tax we're checking.
     * @param  string $item_type Type of the item we're checking, if not a line_item.
     *
     * @return double
     */
    public function get_tax_refunded_for_item($item_id, $tax_id, $item_type = 'line_item');
    /**
     * Get total tax refunded by rate ID.
     *
     * @param  int $rate_id Rate ID.
     *
     * @return float
     */
    public function get_total_tax_refunded_by_rate_id($rate_id);
    /**
     * How much money is left to refund?
     *
     * @return string
     */
    public function get_remaining_refund_amount();
    /**
     * How many items are left to refund?
     *
     * @return int
     */
    public function get_remaining_refund_items();
    /**
     * Get totals for display on pages and in emails.
     *
     * @param string $tax_display Tax to display.
     *
     * @return array
     */
    public function get_order_item_totals($tax_display = '');
    /**
     * Returns the unique ID for this object.
     *
     * @since  2.6.0
     * @return int
     */
    public function get_id();
    /**
     * Method not compatible with WC_Order interface. Returns all metadata for order item.
     *
     * @param $item_id
     * @param bool $load_from_db
     * @return mixed
     */
    public function get_item_meta_data($item_id);
    /**
     * Reduce stock levels for all line items in the order.
     * @deprecated 3.0.0
     */
    public function reduce_order_stock();
}
