<?php

namespace WGPayuVendor\WPDesk\View\Resolver;

use WGPayuVendor\WPDesk\View\Renderer\Renderer;
use WGPayuVendor\WPDesk\View\Resolver\Exception\CanNotResolve;
/**
 * This resolver never finds the file
 *
 * @package WPDesk\View\Resolver
 */
class NullResolver implements \WGPayuVendor\WPDesk\View\Resolver\Resolver
{
    public function resolve($name, \WGPayuVendor\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        throw new \WGPayuVendor\WPDesk\View\Resolver\Exception\CanNotResolve("Null Cannot resolve");
    }
}
