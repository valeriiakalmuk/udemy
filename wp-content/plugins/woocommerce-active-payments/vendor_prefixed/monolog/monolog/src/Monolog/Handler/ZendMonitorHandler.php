<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ActivePaymentsVendor\Monolog\Handler;

use ActivePaymentsVendor\Monolog\Formatter\NormalizerFormatter;
use ActivePaymentsVendor\Monolog\Logger;
/**
 * Handler sending logs to Zend Monitor
 *
 * @author  Christian Bergau <cbergau86@gmail.com>
 * @author  Jason Davis <happydude@jasondavis.net>
 */
class ZendMonitorHandler extends \ActivePaymentsVendor\Monolog\Handler\AbstractProcessingHandler
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
    public function __construct($level = \ActivePaymentsVendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        if (!\function_exists('ActivePaymentsVendor\\zend_monitor_custom_event')) {
            throw new \ActivePaymentsVendor\Monolog\Handler\MissingExtensionException('You must have Zend Server installed with Zend Monitor enabled in order to use this handler');
        }
        //zend monitor constants are not defined if zend monitor is not enabled.
        $this->levelMap = array(\ActivePaymentsVendor\Monolog\Logger::DEBUG => \ActivePaymentsVendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \ActivePaymentsVendor\Monolog\Logger::INFO => \ActivePaymentsVendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \ActivePaymentsVendor\Monolog\Logger::NOTICE => \ActivePaymentsVendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \ActivePaymentsVendor\Monolog\Logger::WARNING => \ActivePaymentsVendor\ZEND_MONITOR_EVENT_SEVERITY_WARNING, \ActivePaymentsVendor\Monolog\Logger::ERROR => \ActivePaymentsVendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \ActivePaymentsVendor\Monolog\Logger::CRITICAL => \ActivePaymentsVendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \ActivePaymentsVendor\Monolog\Logger::ALERT => \ActivePaymentsVendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \ActivePaymentsVendor\Monolog\Logger::EMERGENCY => \ActivePaymentsVendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR);
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->writeZendMonitorCustomEvent(\ActivePaymentsVendor\Monolog\Logger::getLevelName($record['level']), $record['message'], $record['formatted'], $this->levelMap[$record['level']]);
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
        return new \ActivePaymentsVendor\Monolog\Formatter\NormalizerFormatter();
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
