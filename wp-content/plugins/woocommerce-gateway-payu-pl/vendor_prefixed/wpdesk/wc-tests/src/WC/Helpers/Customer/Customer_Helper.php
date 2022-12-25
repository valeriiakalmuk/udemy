<?php

namespace WGPayuVendor\WPDesk\Tests\WC\Helpers\Customer;

class Customer_Helper
{
    const COUNTRY_CODE = 'PL';
    const POSTAL_CODE = '22-100';
    /**
     * @var WC_Customer
     */
    protected $customer;
    /**
     * WPDesk_Woocommerce_Customer_Helper constructor.
     *
     * @param WC_Customer $customer
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
    }
    /**
     *
     */
    public function setup_wc_customer()
    {
        $this->set_billing_country(self::COUNTRY_CODE);
        $this->set_billing_postcode(self::POSTAL_CODE);
        $this->set_shipping_country(self::COUNTRY_CODE);
        $this->set_shipping_postcode(self::POSTAL_CODE);
    }
    /**
     * @param string $country
     *
     */
    public function set_billing_country($country)
    {
        $this->customer->set_billing_country($country);
    }
    /**
     * @param string $postcode
     */
    public function set_billing_postcode($postcode)
    {
        $this->customer->set_billing_postcode($postcode);
    }
    /**
     * @param string $country
     */
    public function set_shipping_country($country)
    {
        $this->customer->set_shipping_country($country);
    }
    /**
     * @param string $postcode
     */
    public function set_shipping_postcode($postcode)
    {
        $this->customer->set_shipping_postcode($postcode);
    }
}
