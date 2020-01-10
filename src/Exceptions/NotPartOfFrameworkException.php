<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Exceptions;

use Exception;

/**
 * Exception for trying to use some feature that isn't implement by framework
 * This type of features has to be implemented by application (using prepared interface
 * or extending its system implementation and adding support for the feature)
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Exceptions
 */
class NotPartOfFrameworkException extends Exception
{
}