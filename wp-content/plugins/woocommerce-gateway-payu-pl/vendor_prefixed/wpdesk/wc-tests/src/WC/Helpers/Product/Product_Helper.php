<?php

namespace WGPayuVendor\WPDesk\Tests\WC\Helpers\Product;

use WGPayuVendor\WPDesk\WC\Helper\Factory;
class Product_Helper
{
    const PRODUCT_PRICE_9 = 9;
    const PRODUCT_PRICE_1 = 1;
    const PRODUCT_PRICE_0_01 = 0.01;
    const PRODUCT_WEIGHT_9 = 9;
    const PRODUCT_WEIGHT_1 = 1;
    const PRODUCT_WEIGHT_0_01 = 0.01;
    const SHIPPING_CLASS_TERM = 'product_shipping_class';
    /**
     * @param float $product_price
     * @param float $product_weight
     * @param int $shipping_class_id
     *
     * @return int
     */
    public function create_product($product_price, $product_weight = 0.0, $shipping_class_id = 0)
    {
        $product = \WGPayuVendor\WC_Helper_Product::create_simple_product();
        $compatibilityFactory = \WGPayuVendor\WPDesk\WC\Helper\Factory::create_compatibility_helper_factory();
        $productHelper = $compatibilityFactory->create_product_helper($product);
        $productHelper->set_price($product_price);
        $productHelper->set_regular_price($product_price);
        $productHelper->set_weight($product_weight);
        if ($shipping_class_id != 0) {
            $productHelper->set_shipping_class_id($shipping_class_id);
        }
        $productHelper->save();
        return $productHelper->get_id();
    }
    /**
     * @param int $product_id
     */
    public function delete_product($product_id)
    {
        \wp_delete_post($product_id, \true);
    }
}
