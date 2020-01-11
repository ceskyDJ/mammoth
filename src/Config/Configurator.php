<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Config;

use JanDrabek\Tracy\GitVersionPanel;
use Mammoth\Common\DIClass;
use Mammoth\Database\DB;
use Mammoth\DI\DIContainer;
use Mammoth\Exceptions\ApplicationNotUseComponentsException;
use Mammoth\Exceptions\ConfigFileNotFoundException;
use Mammoth\Exceptions\LoadNonInjectableClassException;
use Mammoth\Exceptions\NoConfigFileGivenException;
use Mammoth\Exceptions\NonExistingFileException;
use Mammoth\Exceptions\NotSetAllDataInLocalConfigException;
use Mammoth\Http\Factory\CookieFactory;
use Mammoth\Http\Factory\ServerFactory;
use Mammoth\Http\Factory\SessionFactory;
use Nette\Bridges\DatabaseTracy\ConnectionPanel;
use ReflectionException;
use Tracy\Debugger;
use function array_replace_recursive;
use function file_exists;
use function implode;
use function is_array;
use function is_dir;
use function mkdir;
use function parse_ini_file;
use function trigger_error;
use function ucfirst;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const INI_SCANNER_TYPED;

/**
 * config manager
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates
 */
class Configurator
{
    use DIClass;

    /**
     * @var string[] configFiles config files
     */
    private array $configFiles;
    /**
     * @var array configs Loaded configs
     */
    private array $configs;
    /**
     * @var string tempDir Temp file dir
     */
    private string $tempDir;
    /**
     * @var string logDir Log file dir
     */
    private string $logDir;
    /**
     * @var string Root directory of applications sources (where starter.php is)
     */
    private string $appSrcRootDir;

    /**
     * Configurator constructor
     *
     * @param string $appSrcRootDir __DIR__ of starter.php
     * @param string[] $configFiles Absolute paths to config files
     *
     * @throws \Mammoth\Exceptions\NoConfigFileGivenException No config file specified
     * @throws \Mammoth\Exceptions\ConfigFileNotFoundException Invalid configuration file address
     * @throws \Mammoth\Exceptions\NotSetAllDataInLocalConfigException Missing config in local config file
     */
    public function __construct(string $appSrcRootDir, ...$configFiles)
    {
        $this->appSrcRootDir = $appSrcRootDir;
        $this->configFiles = $configFiles;

        $this->configs = $this->getConfigs();
        $this->setDirectoriesFromConfigs();
    }

    /**
     * Returns system configs from saved config files
     *
     * @return array Configs
     * @throws \Mammoth\Exceptions\NoConfigFileGivenException No config file specified
     * @throws \Mammoth\Exceptions\ConfigFileNotFoundException Invalid file address
     * @throws \Mammoth\Exceptions\NotSetAllDataInLocalConfigException Missing config in local config
     *     file
     */
    private function getConfigs(): array
    {
        if (empty($this->configFiles)) {
            throw new NoConfigFileGivenException("No config file has been set.");
        }

        // Add framework default config file
        $configFiles = [__DIR__."/default-config.ini", ...$this->configFiles];

        $configs = [];
        foreach ($configFiles as $file) {
            // config file doesn't exists
            if (!file_exists($file)) {
                throw new ConfigFileNotFoundException("Entered config file's address is invalid");
            }

            $actualFile = parse_ini_file($file, true, INI_SCANNER_TYPED);

            $configs = empty($configs) ? $actualFile : array_replace_recursive($configs, $actualFile);
        }

        // Option overriding control in config files
        // default values style: <...>
        foreach ($configs as $section) {
            if (!is_array($section) && (($notSet = preg_grep("%^<(.*)>$%", $section)) !== [])) {
                throw new NotSetAllDataInLocalConfigException(
                    "This default config value hasn't been replaced: ".implode(", ", $notSet)
                );
            }
        }

        return $configs;
    }

    /**
     * Sets up paths from configs
     */
    private function setDirectoriesFromConfigs(): void
    {
        $this->setTempDir($this->configs['paths']['temp']);
        $this->setLogDir($this->configs['paths']['log']);
    }

    /**
     * Creates a DI container and add base functions to it
     *
     * @return \Mammoth\DI\DIContainer DI container
     */
    public function createContainer(): DIContainer
    {
        $container = new DIContainer($this);

        // Database
        $dbConfig = $this->getDatabaseConfig();
        $container->addInstance(
            new DB($dbConfig['host'], $dbConfig['database'], $dbConfig['user-name'], $dbConfig['user-password'])
        );

        // Factories for constructing HTTP data objects
        /**
         * @var $cookieFactory CookieFactory
         */
        $cookieFactory = $container->getInstance(CookieFactory::class);
        /**
         * @var $serverFactory ServerFactory
         */
        $serverFactory = $container->getInstance(ServerFactory::class);
        /**
         * @var $sessionFactory SessionFactory
         */
        $sessionFactory = $container->getInstance(SessionFactory::class);

        // Tracy
        $this->configureTracy($container);

        return $container->addInstance($this)
            ->addInstance($container)
            ->addInstance($cookieFactory->create())
            ->addInstance($serverFactory->create())
            ->addInstance($sessionFactory->create());
    }

    /**
     * Gets database config
     *
     * @return array Database config
     */
    public function getDatabaseConfig(): array
    {
        return $this->configs['database'];
    }

    /**
     * Configures Tracy bar and paths
     *
     * @param \Mammoth\DI\DIContainer $container DI container
     */
    public function configureTracy(DIContainer $container): void
    {
        try {
            Debugger::getBar()
                ->addPanel(new GitVersionPanel());

            /**
             * @var $dbConnection DB
             */
            $dbConnection = $container->getInstance(DB::class);

            Debugger::getBar()
                ->addPanel(new ConnectionPanel($dbConnection));
        } catch (LoadNonInjectableClassException|ReflectionException $e) {
            // Cannot occur, because it's typed manually
        }

        Debugger::$logDirectory = $this->getLogDir();
        Debugger::$productionMode = !$this->isActualServerDevelopment();
    }

    /**
     * Getter for logDir
     *
     * @return string
     */
    public function getLogDir(): string
    {
        return $this->logDir;
    }

    /**
     * Fluent setter for logDir
     *
     * @param string $logDir
     *
     * @return Configurator
     */
    public function setLogDir(string $logDir): Configurator
    {
        $this->logDir = __DIR__."/../../{$logDir}";

        if (!is_dir($this->logDir)) {
            $this->resolveInvalidDirectoryFromConfig($this->logDir, "log");
        }

        return $this;
    }

    /**
     * Checks if current server is development
     *
     * @return bool Is actual server development?
     */
    public function isActualServerDevelopment(): bool
    {
        $mode = $this->configs['development-server']['mode'];

        if ($mode === true) {
            return true;
        } elseif ($mode === false) {
            return false;
        } else {
            return ($this->configs['development-server']['domain'] === $_SERVER['SERVER_NAME'])
                || Debugger::detectDebugMode();
        }
    }

    /**
     * Getter for tempDir
     *
     * @return string
     */
    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    /**
     * Fluent setter for tempDir
     *
     * @param string $tempDir
     *
     * @return Configurator
     */
    public function setTempDir(string $tempDir): Configurator
    {
        $this->tempDir = __DIR__."/../../{$tempDir}";

        if (!is_dir($this->tempDir)) {
            $this->resolveInvalidDirectoryFromConfig($this->tempDir, "temp");
        }

        return $this;
    }

    /**
     * Gets implement class from input interface
     *
     * @param string|null $interface Abstraction name (without namespace)
     *
     * @return string|null Full qualified implement class name
     */
    public function getImplementClassFromInterface(?string $interface): ?string
    {
        if ($interface === null) {
            return null;
        }

        return $this->configs['injects'][$interface];
    }

    /**
     * Returns route syntax from config
     *
     * @return string Route syntax (formula)
     */
    public function getRoute(): string
    {
        return $this->configs['routing']['route'];
    }

    /**
     * Returns root namespace of application from config
     *
     * @return string Root namespace (vendor part of app's namespace) - ex. "App"
     */
    public function getAppRootNamespace(): string
    {
        return $this->configs['routing']['root-namespace'];
    }

    /**
     * Getter for appSrcRootDir
     *
     * @return string
     */
    public function getAppSrcRootDir(): string
    {
        return $this->appSrcRootDir;
    }

    /**
     * Returns application's default component
     * (if application use component system)
     *
     * @return string Application's default component
     * @throws \Mammoth\Exceptions\ApplicationNotUseComponentsException Application doesn't use components
     */
    public function getAppDefaultComponent(): string
    {
        if (isset($this->configs['routing']['default-component'])) {
            return $this->configs['routing']['default-component'];
        } else {
            throw new ApplicationNotUseComponentsException("Application doesn't use component system");
        }
    }

    /**
     * Returns langs allowed to use in application
     *
     * @return array Valid (allowed) langs
     */
    public function getValidLangs(): array
    {
        return $this->configs['translating']['valid-languages'];
    }

    /**
     * Returns application's directory with templates (views)
     *
     * @return string Path to directory with templates
     */
    public function getAppTemplatesDir(): string
    {
        return $this->configs['paths']['templates'];
    }

    /**
     * Returns application's version from config
     *
     * @return string|null Application's version or null if it's not specified
     */
    public function getAppVersion(): ?string
    {
        return $this->configs['version']['id'];
    }

    /**
     * Returns name of the application's base controller
     *
     * @return string Base controller's name (ex. home)
     */
    public function getAppBaseControllerName(): string
    {
        return mb_strtolower($this->configs['controllers']['base']);
    }

    /**
     * Returns name of the application's error controller
     *
     * @return string Error controller's name (ex. error)
     */
    public function getAppErrorControllerName(): string
    {
        return mb_strtolower($this->configs['controllers']['error']);
    }

    /**
     * Returns application's default language (if application is multilingual)
     *
     * @return string|null Application's default language
     */
    public function getAppDefaultLang(): ?string
    {
        return $this->configs['translating']['default-language'];
    }

    /**
     * Resolves invalid directory loaded from config
     *
     * @param string $dir Loaded directory full path
     * @param string $type Type of directory (ex. log, type, ...)
     */
    private function resolveInvalidDirectoryFromConfig(string $dir, string $type): void
    {
        trigger_error(ucfirst($type)." dir not found (path: {$dir}). Creating...", E_USER_NOTICE);

        $success = @mkdir($dir); // Warning is triggered manually
        if (!$success) {
            trigger_error("Cannot create {$type} dir (path: {$dir}).", E_USER_WARNING);
        }
    }
}
