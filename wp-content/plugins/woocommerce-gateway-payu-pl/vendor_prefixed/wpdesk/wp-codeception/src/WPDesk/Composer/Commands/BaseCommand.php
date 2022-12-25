<?php

namespace WGPayuVendor\WPDesk\Composer\Codeception\Commands;

use WGPayuVendor\Composer\Command\BaseCommand as CodeceptionBaseCommand;
use WGPayuVendor\Symfony\Component\Console\Output\OutputInterface;
/**
 * Base for commands - declares common methods.
 *
 * @package WPDesk\Composer\Codeception\Commands
 */
abstract class BaseCommand extends \WGPayuVendor\Composer\Command\BaseCommand
{
    /**
     * @param string $command
     * @param OutputInterface $output
     */
    protected function execAndOutput($command, \WGPayuVendor\Symfony\Component\Console\Output\OutputInterface $output)
    {
        \passthru($command);
    }
}
