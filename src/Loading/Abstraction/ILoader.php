<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Loading\Abstraction;

/**
 * Loader for auto-loading application classes
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Loading\Abstraction
 */
interface ILoader
{
    /**
     * Starts loading of application classes
     */
    public function startClassAutoLoading(): void;

    /**
     * Adds new namespace for auto-loading
     *
     * @param string $namespaceRoot Root (vendor) namespace part - it's always the first one (for ex. App)
     * @param string $pathRoot Root directory - all classes are in this directory
     *
     * @return \Mammoth\Loading\Abstraction\ILoader
     */
    public function addNamespace(string $namespaceRoot, string $pathRoot): ILoader;
}