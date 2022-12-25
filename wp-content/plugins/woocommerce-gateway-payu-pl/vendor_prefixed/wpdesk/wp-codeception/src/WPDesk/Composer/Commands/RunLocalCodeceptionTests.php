<?php

namespace WGPayuVendor\WPDesk\Composer\Codeception\Commands;

use WGPayuVendor\Symfony\Component\Console\Input\InputArgument;
use WGPayuVendor\Symfony\Component\Console\Input\InputInterface;
use WGPayuVendor\Symfony\Component\Console\Output\OutputInterface;
use WGPayuVendor\Symfony\Component\Yaml\Exception\ParseException;
use WGPayuVendor\Symfony\Component\Yaml\Yaml;
/**
 * Codeception tests run command.
 *
 * @package WPDesk\Composer\Codeception\Commands
 */
class RunLocalCodeceptionTests extends \WGPayuVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests
{
    use LocalCodeceptionTrait;
    /**
     * Configure command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('run-local-codeception-tests')->setDescription('Run local codeception tests.')->setDefinition(array(new \WGPayuVendor\Symfony\Component\Console\Input\InputArgument(self::SINGLE, \WGPayuVendor\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Name of Single test to run.', ' ')));
    }
    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(\WGPayuVendor\Symfony\Component\Console\Input\InputInterface $input, \WGPayuVendor\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $configuration = $this->getWpDeskConfiguration();
        $this->prepareWpConfig($output, $configuration);
        $singleTest = $input->getArgument(self::SINGLE);
        $sep = \DIRECTORY_SEPARATOR;
        $codecept = "vendor{$sep}bin{$sep}codecept";
        $cleanOutput = $codecept . ' clean';
        $this->execAndOutput($cleanOutput, $output);
        $runLocalTests = $codecept . ' run -f --steps --html --verbose acceptance ' . $singleTest;
        $this->execAndOutput($runLocalTests, $output);
    }
}
