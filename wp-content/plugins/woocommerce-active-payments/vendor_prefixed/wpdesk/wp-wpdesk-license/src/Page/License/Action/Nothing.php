<?php

namespace ActivePaymentsVendor\WPDesk\License\Page\License\Action;

use ActivePaymentsVendor\WPDesk\License\Page\Action;
/**
 * Do nothing.
 *
 * @package WPDesk\License\Page\License\Action
 */
class Nothing implements \ActivePaymentsVendor\WPDesk\License\Page\Action
{
    public function execute(array $plugin)
    {
        // NOOP
    }
}
