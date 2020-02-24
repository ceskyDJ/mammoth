<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Exceptions;

use RuntimeException;

/**
 * Exception for error while connecting to DB configured in config files
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Exceptions
 */
class CannotConnectToDatabaseException extends RuntimeException
{
}