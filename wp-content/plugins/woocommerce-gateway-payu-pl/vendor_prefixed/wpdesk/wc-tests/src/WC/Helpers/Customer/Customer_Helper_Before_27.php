<?php

namespace WGPayuVendor\WPDesk\Tests\WC\Helpers\Customer;

class Customer_Helper_Before_27 extends \WGPayuVendor\WPDesk\Tests\WC\Helpers\Customer\Customer_Helper
{
    /**
     * @param string $country
     */
    public function set_billing_country($country)
    {
        $this->customer->country = $country;
    }
    /**
     * @param string $postcode
     */
    public function set_billing_postcode($postcode)
    {
        $this->customer->postcode = $postcode;
    }
    /**
     * @param string $country
     */
    public function set_shipping_country($country)
    {
        $this->customer->shipping_country = $country;
    }
    /**
     * @param string $postcode
     */
    public function set_shipping_postcode($postcode)
    {
        $this->customer->shipping_postcode = $postcode;
    }
}
