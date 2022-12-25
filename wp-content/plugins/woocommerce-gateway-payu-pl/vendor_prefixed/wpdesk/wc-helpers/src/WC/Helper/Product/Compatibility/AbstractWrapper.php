<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Product\Compatibility;

abstract class AbstractWrapper
{
    /** @var \WC_Product */
    protected $product;
    /**
     * AbstractWrapper constructor.
     *
     * @param \WC_Order $order
     */
    public function __construct(\WC_Product $product)
    {
        $this->product = $product;
    }
    /**
     * @param $order
     *
     * @return int
     */
    static function get_product_id(\WC_Product $product)
    {
        return \method_exists($product, 'get_id') ? $product->get_id() : $product->id;
    }
}
