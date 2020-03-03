<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Loading;

use Mammoth\Config\Configurator;
use Mammoth\DI\DIClass;
use Mammoth\Exceptions\LoadingForeignClassException;
use Mammoth\Loading\Abstraction\ILoader;
use function array_shift;
use function key_exists;
use function ltrim;
use function preg_match;
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
     * @var array Known namespaces to loading
     */
    private array $namespaces = [];

    /**
     * @inheritDoc
     * @noinspection PhpIncludeInspection Include has to be full automatic
     */
    public function startClassAutoLoading(): void
    {
        // Add default app's namespace
        // Controller\User\HomeController -> .../src/Controller/User/HomeController.php
        $this->addNamespace($this->configurator->getAppRootNamespace(), $this->configurator->getAppSrcRootDir());

        spl_autoload_register(fn($className) => require_once $this->getClassPathFromNamespace($className));
    }

    /**
     * @inheritDoc
     */
    public function addNamespace(string $namespaceRoot, string $pathRoot): ILoader
    {
        $this->namespaces[$namespaceRoot] = $pathRoot;

        return $this;
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
        preg_match("%^[A-Za-z]+\\\\%", $classNamespace, $namespace);

        $namespaceRoot = rtrim(array_shift($namespace), "\\");

        if (key_exists($namespaceRoot, $this->namespaces) === false) {
            throw new LoadingForeignClassException(
                "Class {$classNamespace} isn't part of application nor any of other supported root namespaces"
            );
        }

        return $this->getPathFromNamespace($classNamespace, $namespaceRoot, $this->namespaces[$namespaceRoot]);
    }

    /**
     * Returns class path for application classes
     *
     * @param string $classNamespace Class's full-qualified namespace
     * @param string $rootNamespace Root namespace for remove (for ex. App)
     * @param string $rootPath Root path - main directory for class loading, every address starts here
     *
     * @return string Absolute class path
     */
    private function getPathFromNamespace(string $classNamespace, string $rootNamespace, string $rootPath): string
    {
        $classNamespace = ltrim($classNamespace, "\\");
        $classNamespace = str_replace("{$rootNamespace}\\", "", $classNamespace);

        $relativePath = str_replace("\\", "/", $classNamespace);

        return "{$rootPath}/{$relativePath}.php";
    }
}