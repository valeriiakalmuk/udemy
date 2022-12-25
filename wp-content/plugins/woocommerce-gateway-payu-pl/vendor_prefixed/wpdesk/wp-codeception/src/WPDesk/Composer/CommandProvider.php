<?php

namespace WGPayuVendor\WPDesk\Composer\Codeception;

use WGPayuVendor\WPDesk\Composer\Codeception\Commands\CreateCodeceptionTests;
use WGPayuVendor\WPDesk\Composer\Codeception\Commands\PrepareCodeceptionDb;
use WGPayuVendor\WPDesk\Composer\Codeception\Commands\PrepareLocalCodeceptionTests;
use WGPayuVendor\WPDesk\Composer\Codeception\Commands\PrepareParallelCodeceptionTests;
use WGPayuVendor\WPDesk\Composer\Codeception\Commands\PrepareWordpressForCodeception;
use WGPayuVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests;
use WGPayuVendor\WPDesk\Composer\Codeception\Commands\RunLocalCodeceptionTests;
/**
 * Links plugin commands handlers to composer.
 */
class CommandProvider implements \WGPayuVendor\Composer\Plugin\Capability\CommandProvider
{
    public function getCommands()
    {
        return [new \WGPayuVendor\WPDesk\Composer\Codeception\Commands\CreateCodeceptionTests(), new \WGPayuVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests(), new \WGPayuVendor\WPDesk\Composer\Codeception\Commands\RunLocalCodeceptionTests(), new \WGPayuVendor\WPDesk\Composer\Codeception\Commands\PrepareCodeceptionDb(), new \WGPayuVendor\WPDesk\Composer\Codeception\Commands\PrepareWordpressForCodeception(), new \WGPayuVendor\WPDesk\Composer\Codeception\Commands\PrepareLocalCodeceptionTests(), new \WGPayuVendor\WPDesk\Composer\Codeception\Commands\PrepareParallelCodeceptionTests()];
    }
}
