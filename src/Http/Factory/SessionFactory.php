<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Factory;

use Mammoth\Http\Entity\Session;

/**
 * Factory for creating Session instance
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Http\Factory
 */
class SessionFactory
{
    /**
     * Creates Session instance
     *
     * @return \Mammoth\Http\Entity\Session Session instance
     */
    public function create(): Session
    {
        return new Session($_SESSION);
    }
}