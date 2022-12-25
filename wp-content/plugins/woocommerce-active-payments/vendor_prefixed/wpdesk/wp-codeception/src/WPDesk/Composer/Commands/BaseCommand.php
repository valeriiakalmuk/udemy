<?php

namespace ActivePaymentsVendor\WPDesk\Composer\Codeception\Commands;

use ActivePaymentsVendor\Composer\Command\BaseCommand as CodeceptionBaseCommand;
use ActivePaymentsVendor\Symfony\Component\Console\Output\OutputInterface;
/**
 * Base for commands - declares common methods.
 *
 * @package WPDesk\Composer\Codeception\Commands
 */
abstract class BaseCommand extends \ActivePaymentsVendor\Composer\Command\BaseCommand
{
    /**
     * @param string $command
     * @param OutputInterface $output
     */
    protected function execAndOutput($command, \ActivePaymentsVendor\Symfony\Component\Console\Output\OutputInterface $output)
    {
        \passthru($command);
    }
}
