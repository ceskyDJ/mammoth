<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Logging\Abstraction;

use Tracy;

/**
 * Logger for logging (saving to some storage) some error things and more
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Abstraction
 */
interface ILogger extends Tracy\ILogger
{
}