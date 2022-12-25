<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Product\Compatibility;

use WGPayuVendor\WPDesk\WC\Helper\Product\ProductCompatible;
class LegacyV33 extends \WGPayuVendor\WPDesk\WC\Helper\Product\Compatibility\AbstractWrapper implements \WGPayuVendor\WPDesk\WC\Helper\Product\ProductCompatible
{
    public function save()
    {
        return $this->product->save();
    }
    public function get_post_data()
    {
        return $this->product->get_post_data();
    }
    public function get_id()
    {
        return $this->product->get_id();
    }
    public function get_parent_id($context = 'view')
    {
        return $this->product->get_parent_id($context);
    }
    public function get_stock_status($context = 'view')
    {
        return $this->product->get_stock_status($context);
    }
    public function set_price($price)
    {
        $this->product->set_price($price);
    }
    public function set_regular_price($regular_price)
    {
        $this->product->set_regular_price($regular_price);
    }
    public function set_weight($weight)
    {
        $this->product->set_weight($weight);
    }
    public function set_shipping_class_id($shipping_class_id)
    {
        $this->product->set_shipping_class_id($shipping_class_id);
    }
}
