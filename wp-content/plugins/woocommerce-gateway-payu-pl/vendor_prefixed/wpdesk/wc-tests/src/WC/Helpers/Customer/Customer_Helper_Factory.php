<?php

namespace WGPayuVendor\WPDesk\Tests\WC\Helpers\Customer;

class Customer_Helper_Factory
{
    /**
     * @param string $wc_version
     * @param WC_Customer $customer
     *
     * @return Customer_Helper
     */
    public static function create_helper_for_wc($wc_version, $customer)
    {
        if (\version_compare($wc_version, '2.7', '<')) {
            return new \WGPayuVendor\WPDesk\Tests\WC\Helpers\Customer\Customer_Helper_Before_27($customer);
        } else {
            return new \WGPayuVendor\WPDesk\Tests\WC\Helpers\Customer\Customer_Helper($customer);
        }
    }
}
