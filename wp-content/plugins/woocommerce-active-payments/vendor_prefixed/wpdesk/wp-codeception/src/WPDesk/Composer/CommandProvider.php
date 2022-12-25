<?php

namespace ActivePaymentsVendor\WPDesk\Composer\Codeception;

use ActivePaymentsVendor\WPDesk\Composer\Codeception\Commands\CreateCodeceptionTests;
use ActivePaymentsVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests;
/**
 * Links plugin commands handlers to composer.
 */
class CommandProvider implements \ActivePaymentsVendor\Composer\Plugin\Capability\CommandProvider
{
    public function getCommands()
    {
        return [new \ActivePaymentsVendor\WPDesk\Composer\Codeception\Commands\CreateCodeceptionTests(), new \ActivePaymentsVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests()];
    }
}
