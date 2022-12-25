<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Compatibility;

use WGPayuVendor\WPDesk\WC\Helper\Order\OrderCompatible;
use WGPayuVendor\WPDesk\WC\Helper\Product\ProductCompatible;
interface HelperFactory
{
    /**
     * @param \WC_Product $product
     *
     * @return ProductCompatible
     */
    public function create_product_helper(\WC_Product $product);
    /**
     * @param \WC_Product $product
     *
     * @return OrderCompatible
     */
    public function create_order_helper(\WC_Order $order);
}
