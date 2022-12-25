<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Compatibility;

use WGPayuVendor\WPDesk\WC\Helper\Order\OrderCompatible;
use WGPayuVendor\WPDesk\WC\Helper\Product\ProductCompatible;
class HelperFactoryLegacyV27 implements \WGPayuVendor\WPDesk\WC\Helper\Compatibility\HelperFactory
{
    /**
     * @param \WC_Product $product
     *
     * @return ProductCompatible
     */
    public function create_product_helper(\WC_Product $product)
    {
        return new \WGPayuVendor\WPDesk\WC\Helper\Product\Compatibility\LegacyV27($product);
    }
    /**
     * @param \WC_Order $order
     *
     * @return OrderCompatible
     */
    public function create_order_helper(\WC_Order $order)
    {
        return new \WGPayuVendor\WPDesk\WC\Helper\Order\Compatibility\LegacyV27($order);
    }
}
