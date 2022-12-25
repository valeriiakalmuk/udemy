<?php

namespace WGPayuVendor\WPDesk\WC\Helper;

use WGPayuVendor\WPDesk\WC\Helper\Compatibility\HelperFactory;
use WGPayuVendor\WPDesk\WC\Helper\Compatibility\HelperFactoryLegacyV33;
use WGPayuVendor\WPDesk\WC\Helper\Compatibility\HelperFactoryLegacyV27;
class Factory
{
    /**
     * @param $version
     *
     * @return HelperFactory
     */
    public static function create_compatibility_helper_factory($version = \WC_VERSION)
    {
        if (\version_compare($version, '2.7', '<')) {
            return new \WGPayuVendor\WPDesk\WC\Helper\Compatibility\HelperFactoryLegacyV27();
        } else {
            return new \WGPayuVendor\WPDesk\WC\Helper\Compatibility\HelperFactoryLegacyV33();
        }
    }
}
