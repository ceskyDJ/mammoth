<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Factory;

use Mammoth\Http\Entity\Cookie;

/**
 * Factory for creating Cookie instances
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Http\Factory
 */
class CookieFactory
{
    /**
     * Creates Cookie instance
     *
     * @return \Mammoth\Http\Entity\Cookie Cookie instance
     */
    public function create(): Cookie
    {
        return new Cookie($_COOKIE);
    }
}