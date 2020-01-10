<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Logging;

use Mammoth\Common\DIClass;
use Mammoth\Logging\Abstraction\ILogger;
use Tracy\Debugger;

/**
 * Logger
 * Now it's using Tracy build-in logger, this class is only mediator to run it well
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates
 */
class Logger implements ILogger
{
    use DIClass;

    /**
     * Logs something
     *
     * @param mixed $value Value to put into log file
     * @param string $level Log level (info, warning, error, ...)
     */
    function log($value, $level = self::INFO)
    {
        Debugger::getLogger()
            ->log($value, $level);
    }
}