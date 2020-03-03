<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Exceptions;

use RuntimeException;

/**
 * Exception for try to autoload foreign class (class from not recognized root namespace)
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Exceptions
 */
class LoadingForeignClassException extends RuntimeException
{
}