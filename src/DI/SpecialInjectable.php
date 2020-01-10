<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\DI;

use ReflectionException;

/**
 * Abstraction determining class with manual injecting allowed
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\DI
 */
interface SpecialInjectable
{
    /**
     * Injects dependencies manually
     *
     * @param DIContainer $diContainer DI container instance
     *
     * @throws ReflectionException Non existing class
     */
    public function inject(DIContainer $diContainer): void;
}