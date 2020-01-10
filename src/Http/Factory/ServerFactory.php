<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Http\Factory;

use Mammoth\Http\Entity\Server;

/**
 * Factory for creating Server instance
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Http\Factory
 */
class ServerFactory
{
    /**
     * Creates Server instance
     *
     * @return \Mammoth\Http\Entity\Server Server instance
     */
    public function create(): Server
    {
        return new Server($_SERVER);
    }
}