<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Exceptions;

use RuntimeException;

/**
 * Exception for user properties array with one or more members that aren't instance of UserData class
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Exceptions
 */
class InvalidUserPropertiesArrayException extends RuntimeException
{
}