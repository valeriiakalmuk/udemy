<?php

namespace WGPayuVendor\WPDesk\Composer\Codeception\Commands;

use WGPayuVendor\Composer\Downloader\FilesystemException;
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
class PrepareLocalCodeceptionTests extends \WGPayuVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests
{
    use LocalCodeceptionTrait;
    /**
     * Configure command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('prepare-local-codeception-tests')->setDescription('Prepare local codeception tests.');
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
        $this->installPlugin($configuration->getPluginDir(), $output, $configuration);
        $this->activatePlugins($output, $configuration);
        $this->prepareWpConfig($output, $configuration);
        $this->copyThemeFiles($configuration->getThemeFiles(), $configuration->getApacheDocumentRoot() . '/wp-content/themes/storefront-wpdesk-tests');
        $sep = \DIRECTORY_SEPARATOR;
        $codecept = "vendor{$sep}bin{$sep}codecept";
        $cleanOutput = $codecept . ' clean';
        $this->execAndOutput($cleanOutput, $output);
    }
    /**
     * @param array $theme_files
     * @param $theme_folder
     *
     * @throws FilesystemException
     */
    private function copyThemeFiles(array $theme_files, $theme_folder)
    {
        foreach ($theme_files as $theme_file) {
            if (!\copy($theme_file, $this->trailingslashit($theme_folder) . \basename($theme_file))) {
                throw new \WGPayuVendor\Composer\Downloader\FilesystemException('Error copying theme file: ' . $theme_file);
            }
        }
    }
}
