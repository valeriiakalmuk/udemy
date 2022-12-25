<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Product\Compatibility;

use WGPayuVendor\WPDesk\WC\Helper\Compatibility\Traits\PostMetaDataManagement;
use WGPayuVendor\WPDesk\WC\Helper\Product\ProductCompatible;
class LegacyV27 extends \WGPayuVendor\WPDesk\WC\Helper\Product\Compatibility\AbstractWrapper implements \WGPayuVendor\WPDesk\WC\Helper\Product\ProductCompatible
{
    use PostMetaDataManagement;
    const META_NAME_PARENT_ID = '_parent_id';
    const META_NAME_STOCK_STATUS = '_stock_status';
    const SHIPPING_CLASS_TERM = 'product_shipping_class';
    public function __construct(\WC_Product $product)
    {
        parent::__construct($product);
        $this->post_id = static::get_product_id($product);
    }
    public function get_post_data()
    {
        if ($this->product->is_type('variation')) {
            $post_data = \get_post($this->get_parent_id());
        } else {
            $post_data = \get_post($this->get_id());
        }
        return $post_data;
    }
    public function get_id()
    {
        return parent::get_product_id($this->product);
    }
    public function get_parent_id($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_PARENT_ID, \true);
    }
    public function get_stock_status($context = 'view')
    {
        return $this->get_post_meta(self::META_NAME_STOCK_STATUS, \true);
    }
    public function set_price($price)
    {
        $this->set_post_meta('_price', $price);
    }
    public function set_regular_price($regular_price)
    {
        $this->set_post_meta('_regular_price', $regular_price);
    }
    public function set_weight($weight)
    {
        $this->set_post_meta('_weight', $weight);
    }
    public function set_shipping_class_id($shipping_class_id)
    {
        \wp_set_object_terms($this->post_id, $shipping_class_id, self::SHIPPING_CLASS_TERM);
    }
}
