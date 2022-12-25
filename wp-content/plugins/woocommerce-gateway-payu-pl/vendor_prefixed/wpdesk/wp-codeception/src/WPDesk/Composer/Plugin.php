<?php

namespace WGPayuVendor\WPDesk\Composer\Codeception;

use WGPayuVendor\Composer\Composer;
use WGPayuVendor\Composer\IO\IOInterface;
use WGPayuVendor\Composer\Plugin\Capable;
use WGPayuVendor\Composer\Plugin\PluginInterface;
/**
 * Composer plugin.
 *
 * @package WPDesk\Composer\Codeception
 */
class Plugin implements \WGPayuVendor\Composer\Plugin\PluginInterface, \WGPayuVendor\Composer\Plugin\Capable
{
    /**
     * @var Composer
     */
    private $composer;
    /**
     * @var IOInterface
     */
    private $io;
    public function activate(\WGPayuVendor\Composer\Composer $composer, \WGPayuVendor\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    /**
     * @inheritDoc
     */
    public function deactivate(\WGPayuVendor\Composer\Composer $composer, \WGPayuVendor\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    /**
     * @inheritDoc
     */
    public function uninstall(\WGPayuVendor\Composer\Composer $composer, \WGPayuVendor\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    public function getCapabilities()
    {
        return [\WGPayuVendor\Composer\Plugin\Capability\CommandProvider::class => \WGPayuVendor\WPDesk\Composer\Codeception\CommandProvider::class];
    }
}
