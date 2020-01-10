<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Exceptions;

use Exception;

/**
 * Exception for injecting class that isn't injectable automatically
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Exceptions
 */
class LoadNonInjectableClassException extends Exception
{
}