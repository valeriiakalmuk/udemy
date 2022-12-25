<?php

namespace ActivePaymentsVendor\WPDesk\Composer\Codeception;

use ActivePaymentsVendor\Composer\Composer;
use ActivePaymentsVendor\Composer\IO\IOInterface;
use ActivePaymentsVendor\Composer\Plugin\Capable;
use ActivePaymentsVendor\Composer\Plugin\PluginInterface;
/**
 * Composer plugin.
 *
 * @package WPDesk\Composer\Codeception
 */
class Plugin implements \ActivePaymentsVendor\Composer\Plugin\PluginInterface, \ActivePaymentsVendor\Composer\Plugin\Capable
{
    /**
     * @var Composer
     */
    private $composer;
    /**
     * @var IOInterface
     */
    private $io;
    public function activate(\ActivePaymentsVendor\Composer\Composer $composer, \ActivePaymentsVendor\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    public function getCapabilities()
    {
        return [\ActivePaymentsVendor\Composer\Plugin\Capability\CommandProvider::class => \ActivePaymentsVendor\WPDesk\Composer\Codeception\CommandProvider::class];
    }
}
