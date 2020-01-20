<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Loading;

use Mammoth\Config\Configurator;
use Mammoth\DI\DIClass;
use Mammoth\Loading\Abstraction\ILoader;
use function ltrim;
use function spl_autoload_register;
use function str_replace;

/**
 * Loader for auto-loading application classes
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Loading
 */
class Loader implements ILoader
{
    use DIClass;

    /**
     * @inject
     */
    private Configurator $configurator;

    /**
     * @inheritDoc
     * @noinspection PhpIncludeInspection Include has to be full automatic
     */
    public function startClassAutoLoading(): void
    {
        spl_autoload_register(fn($className) => require_once $this->getClassPathFromNamespace($className));
    }

    /**
     * Returns class path from its namespace
     *
     * @param string $classNamespace Class's full-qualified namespace
     *
     * @return string Absolute class path
     */
    private function getClassPathFromNamespace(string $classNamespace): string
    {
        // Controller\User\HomeController -> .../src/Controller/User/HomeController.php
        $classNamespace = ltrim($classNamespace, "\\");
        $classNamespace = str_replace($this->configurator->getAppRootNamespace()."\\", "", $classNamespace);

        $relativePath = str_replace("\\", "/", $classNamespace);

        return $this->configurator->getAppSrcRootDir()."/{$relativePath}.php";
    }
}