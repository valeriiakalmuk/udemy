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

use WGPayuVendor\Monolog\Logger;
use WGPayuVendor\Monolog\Formatter\NormalizerFormatter;
use WGPayuVendor\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \WGPayuVendor\Monolog\Handler\AbstractProcessingHandler
{
    private $client;
    public function __construct(\WGPayuVendor\Doctrine\CouchDB\CouchDBClient $client, $level = \WGPayuVendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter()
    {
        return new \WGPayuVendor\Monolog\Formatter\NormalizerFormatter();
    }
}
