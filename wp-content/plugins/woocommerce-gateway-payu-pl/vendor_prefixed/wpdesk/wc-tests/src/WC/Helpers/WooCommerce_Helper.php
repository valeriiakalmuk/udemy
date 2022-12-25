<?php

namespace WGPayuVendor\WPDesk\Tests\WC\Helpers;

class WooCommerce_Helper
{
    const WOOCOMMERCE_CURRENCY_PLN = 'PLN';
    const COUNTRY_CODE = 'PL';
    const POSTAL_CODE = '22-100';
    const PRICES_INCLUDES_TAX = 'yes';
    const PRICES_DO_NOT_INCLUDES_TAX = 'no';
    const DISPLAY_PRICES_WITH_TAX = 'incl';
    const DISPLAY_PRICES_WITHOUT_TAX = 'excl';
    /**
     * Setup Woocommerce: country, currency, taxes, etc.
     *
     */
    public function setup_woocommerce()
    {
        \update_option('woocommerce_default_country', static::COUNTRY_CODE);
        \update_option('woocommerce_default_customer_address', 'base');
        \update_option('woocommerce_calc_taxes', 'yes');
        \update_option('woocommerce_tax_based_on', 'billing');
        \update_option('woocommerce_default_country', static::COUNTRY_CODE);
        \update_option('woocommerce_prices_include_tax', static::PRICES_INCLUDES_TAX);
        \update_option('woocommerce_tax_display_cart', static::DISPLAY_PRICES_WITH_TAX);
        \update_option('woocommerce_currency', static::WOOCOMMERCE_CURRENCY_PLN);
        \update_option('woocommerce_tax_round_at_subtotal', 'yes');
        $tax_rate = array('tax_rate_country' => static::COUNTRY_CODE, 'tax_rate_state' => '*', 'tax_rate' => '23.0000', 'tax_rate_name' => 'VAT 23', 'tax_rate_priority' => '1', 'tax_rate_compound' => '0', 'tax_rate_shipping' => '1', 'tax_rate_order' => '1', 'tax_rate_class' => '');
        \WC_Tax::_insert_tax_rate($tax_rate);
        $tax_rate = array('tax_rate_country' => static::COUNTRY_CODE, 'tax_rate_state' => '*', 'tax_rate' => '8.0000', 'tax_rate_name' => 'VAT 8', 'tax_rate_priority' => '1', 'tax_rate_compound' => '0', 'tax_rate_shipping' => '1', 'tax_rate_order' => '1', 'tax_rate_class' => '');
        \WC_Tax::_insert_tax_rate($tax_rate);
    }
    /**
     *
     */
    public function clear_woocommerce()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_tax_rates");
        $wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_tax_rate_locations");
    }
}
