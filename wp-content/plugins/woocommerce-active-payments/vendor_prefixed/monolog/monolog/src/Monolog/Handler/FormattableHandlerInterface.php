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
namespace ActivePaymentsVendor\Monolog\Handler;

use ActivePaymentsVendor\Monolog\Formatter\FormatterInterface;
/**
 * Interface to describe loggers that have a formatter
 *
 * This interface is present in monolog 1.x to ease forward compatibility.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface FormattableHandlerInterface
{
    /**
     * Sets the formatter.
     *
     * @param  FormatterInterface $formatter
     * @return HandlerInterface   self
     */
    public function setFormatter(\ActivePaymentsVendor\Monolog\Formatter\FormatterInterface $formatter) : \ActivePaymentsVendor\Monolog\Handler\HandlerInterface;
    /**
     * Gets the formatter.
     *
     * @return FormatterInterface
     */
    public function getFormatter() : \ActivePaymentsVendor\Monolog\Formatter\FormatterInterface;
}
