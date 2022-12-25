<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WGPayuVendor\Monolog\Handler;

use WGPayuVendor\Monolog\Formatter\FormatterInterface;
use WGPayuVendor\Monolog\Formatter\LineFormatter;
/**
 * Helper trait for implementing FormattableInterface
 *
 * This trait is present in monolog 1.x to ease forward compatibility.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
trait FormattableHandlerTrait
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;
    /**
     * {@inheritdoc}
     * @suppress PhanTypeMismatchReturn
     */
    public function setFormatter(\WGPayuVendor\Monolog\Formatter\FormatterInterface $formatter) : \WGPayuVendor\Monolog\Handler\HandlerInterface
    {
        $this->formatter = $formatter;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getFormatter() : \WGPayuVendor\Monolog\Formatter\FormatterInterface
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }
        return $this->formatter;
    }
    /**
     * Gets the default formatter.
     *
     * Overwrite this if the LineFormatter is not a good default for your handler.
     */
    protected function getDefaultFormatter() : \WGPayuVendor\Monolog\Formatter\FormatterInterface
    {
        return new \WGPayuVendor\Monolog\Formatter\LineFormatter();
    }
}
