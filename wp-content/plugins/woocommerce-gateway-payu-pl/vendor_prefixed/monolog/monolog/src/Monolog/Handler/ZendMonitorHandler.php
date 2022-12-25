<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WGPayuVendor\Monolog\Handler;

use WGPayuVendor\Monolog\Formatter\NormalizerFormatter;
use WGPayuVendor\Monolog\Logger;
/**
 * Handler sending logs to Zend Monitor
 *
 * @author  Christian Bergau <cbergau86@gmail.com>
 * @author  Jason Davis <happydude@jasondavis.net>
 */
class ZendMonitorHandler extends \WGPayuVendor\Monolog\Handler\AbstractProcessingHandler
{
    /**
     * Monolog level / ZendMonitor Custom Event priority map
     *
     * @var array
     */
    protected $levelMap = array();
    /**
     * Construct
     *
     * @param  int                       $level
     * @param  bool                      $bubble
     * @throws MissingExtensionException
     */
    public function __construct($level = \WGPayuVendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        if (!\function_exists('WGPayuVendor\\zend_monitor_custom_event')) {
            throw new \WGPayuVendor\Monolog\Handler\MissingExtensionException('You must have Zend Server installed with Zend Monitor enabled in order to use this handler');
        }
        //zend monitor constants are not defined if zend monitor is not enabled.
        $this->levelMap = array(\WGPayuVendor\Monolog\Logger::DEBUG => \WGPayuVendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \WGPayuVendor\Monolog\Logger::INFO => \WGPayuVendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \WGPayuVendor\Monolog\Logger::NOTICE => \WGPayuVendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \WGPayuVendor\Monolog\Logger::WARNING => \WGPayuVendor\ZEND_MONITOR_EVENT_SEVERITY_WARNING, \WGPayuVendor\Monolog\Logger::ERROR => \WGPayuVendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \WGPayuVendor\Monolog\Logger::CRITICAL => \WGPayuVendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \WGPayuVendor\Monolog\Logger::ALERT => \WGPayuVendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \WGPayuVendor\Monolog\Logger::EMERGENCY => \WGPayuVendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR);
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->writeZendMonitorCustomEvent(\WGPayuVendor\Monolog\Logger::getLevelName($record['level']), $record['message'], $record['formatted'], $this->levelMap[$record['level']]);
    }
    /**
     * Write to Zend Monitor Events
     * @param string $type Text displayed in "Class Name (custom)" field
     * @param string $message Text displayed in "Error String"
     * @param mixed $formatted Displayed in Custom Variables tab
     * @param int $severity Set the event severity level (-1,0,1)
     */
    protected function writeZendMonitorCustomEvent($type, $message, $formatted, $severity)
    {
        zend_monitor_custom_event($type, $message, $formatted, $severity);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefaultFormatter()
    {
        return new \WGPayuVendor\Monolog\Formatter\NormalizerFormatter();
    }
    /**
     * Get the level map
     *
     * @return array
     */
    public function getLevelMap()
    {
        return $this->levelMap;
    }
}
