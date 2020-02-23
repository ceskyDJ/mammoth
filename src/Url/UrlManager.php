<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Url;

use Mammoth\Config\Configurator;
use Mammoth\DI\DIClass;
use Mammoth\Exceptions\ApplicationNotUseComponentsException;
use Mammoth\Http\Entity\Server;
use Mammoth\Translating\Abstraction\ITranslateManager;
use Mammoth\Url\Abstraction\IUrlManager;
use Mammoth\Url\Entity\ParsedUrl;
use Mammoth\Utils\ArrayHelper;
use Mammoth\Utils\CommonParser;
use Mammoth\Utils\StringManipulator;
use ReflectionClass;
use ReflectionException;
use function array_filter;
use function array_map;
use function bdump;
use function dump;
use function explode;
use function filter_input;
use function implode;
use function is_array;
use function is_dir;
use function is_file;
use function preg_match;
use function str_replace;
use function trim;
use function ucfirst;
use const INPUT_GET;

/**
 * URL routes manager (helps system to recognize URL, translate Parsed Url object to address and more)
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates
 */
class UrlManager implements IUrlManager
{
    use DIClass;

    /**
     * @inject
     */
    private Configurator $configurator;
    /**
     * @inject
     */
    private ArrayHelper $arrayHelper;
    /**
     * @inject
     */
    private CommonParser $commonParser;
    /**
     * @inject
     */
    private ITranslateManager $translateManager;
    /**
     * @inject
     */
    private StringManipulator $stringManipulator;
    /**
     * @inject
     */
    private Server $server;

    /**
     * @inheritDoc
     */
    public function getResultAddress(string $startAddress): string
    {
        return $this->constructAddressFromParsedUrl($this->parseUrl($startAddress));
    }

    /**
     * Constructs address from parsed URL
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object
     *
     * @return string URL address (without protocol, domain) in string form (from data stored in ParsedUrl)
     */
    public function constructAddressFromParsedUrl(ParsedUrl $parsedUrl): string
    {
        $addressArray = [];
        foreach ($parsedUrl->getRouteArray() as $routeItem) {
            $urlItem = $parsedUrl->{"get".ucfirst($routeItem)}();

            if ($urlItem !== null) {
                if (!is_array($urlItem)) {
                    $addressArray[] = $urlItem;
                } else {
                    $addressArray[] = implode("/", $urlItem);
                }
            }
        }

        return $this->stringManipulator->camelCaseToDashes(implode("/", $addressArray));
    }

    /**
     * @inheritDoc
     */
    public function parseUrl(string $url): ParsedUrl
    {
        $routeArray = $this->routeToArray($this->configurator->getRoute());
        $urlArray = $this->urlToArray($url);

        // Simple array of routes (ex. [language, data]) for ParsedUrl
        $basicRouteArray = array_map(fn($item) => $item['name'], $routeArray);

        $parsedUrl = new ParsedUrl($basicRouteArray);
        $urlIndex = 0;
        $urlSize = count($urlArray);
        $routeSize = count($routeArray);

        for ($i = 0; $i < $routeSize; $i++) {
            // If there's no more items in URL, it doesn't make sense to continue
            if ($urlIndex >= $urlSize) {
                break;
            }

            $routeItem = $routeArray[$i];

            $validMethod = "isValid".ucfirst($routeItem['name']);
            $setMethod = "set".ucfirst($routeItem['name']);

            if ($this->$validMethod($urlArray[$urlIndex], $parsedUrl, $routeItem['required']) === true) {
                // Set value to ParsedUrl
                if ($routeItem['name'] !== "data") {
                    // Convert multi-word URL parts to the right form
                    $value = $this->stringManipulator->dashesToCamelCase($urlArray[$urlIndex]);

                    // Action can has 2 variants - normal and for AJAX (with Ajax suffix)
                    if ($routeItem['name'] === "action") {
                        $value = ($this->server->isItAjaxRequest() === false ? $value : $value."Ajax");
                    }

                    $parsedUrl->$setMethod($value);
                } else {
                    $parsedUrl->addData($this->commonParser->autoParse($urlArray[$urlIndex]));

                    // Data can be composed of more URL parts
                    // So, try another cycle
                    // If it's not a fact, first condition in this for cycle stops it
                    $i--;
                }

                // Move in URL array
                $urlIndex++;
            } else {
                // This URL part isn't required
                if ($routeItem['required'] === true) {
                    // Move to another URL array key (value on actual key is bad)
                    // When route item is required, there is a chance it's good
                    // but for different route
                    $urlIndex++;
                }
            }
        }

        // Repairing null ParsedUrl properties
        foreach ($routeArray as $routeItem) {
            $getMethod = "get".ucfirst($routeItem['name']);

            // Property need to repair
            if ($parsedUrl->$getMethod() === null || $parsedUrl->$getMethod() === ParsedUrl::BAD_VALUE) {
                $repairMethod = "repair".ucfirst($routeItem['name']);
                $setMethod = "set".ucfirst($routeItem['name']);

                // Try to repair URL part
                $repairedValue = $this->$repairMethod($parsedUrl, $routeItem['required']);
                $parsedUrl->$setMethod($repairedValue);
            }
        }

        return $parsedUrl;
    }

    /**
     * Makes an array from route
     * It does some things need for next work with route array
     *
     * @param string $route Route in string variant
     *
     * @return array Route as array
     */
    private function routeToArray(string $route): array
    {
        // To array
        $routeArray = explode("/", $route);

        // Lower case
        $routeArray = $this->arrayHelper->changeValuesCase($routeArray);

        // Without % and []
        foreach ($routeArray as &$item) {
            $item = [
                'name'     => str_replace(["%", "[", "]"], "", $item),
                'required' => !preg_match("%\[.+]%", $item),
            ];
        }

        return $routeArray;
    }

    /**
     * Converts URL string to array
     * It also deletes empty parts of it
     *
     * @param string $url URL string form
     *
     * @return array URL as array
     */
    private function urlToArray(string $url): array
    {
        // Empty URL ("") goes to empty array
        if (empty($url)) {
            return [];
        }

        // Remove GET data from URL
        [$url,] = explode("?", $url);

        // Remove white spaces and useless slashes from start and end of url string
        $url = trim($url, "/ \t\n\r\0\x0B");

        $urlArray = explode("/", $url);

        return array_filter($urlArray, fn($value) => !empty($value));
    }

    /**
     * Verifies that the lang is valid for the application
     *
     * @param string|null $lang Lang for verify
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     *
     * @return bool Is it valid?
     */
    private function isValidLanguage(?string $lang, ParsedUrl $parsedUrl): bool
    {
        if ($lang === null) {
            return false;
        }

        if ($this->translateManager->isValidLang($lang)) {
            return true;
        } else {
            $parsedUrl->setLanguage(ParsedUrl::BAD_VALUE);

            return false;
        }
    }

    /**
     * Verifies that the component is valid for the application
     *
     * @param string|null $component Component for verify
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     *
     * @return bool Is it valid?
     */
    private function isValidComponent(?string $component, ParsedUrl $parsedUrl): bool
    {
        if ($component === null) {
            return false;
        }

        try {
            $this->configurator->getAppDefaultComponent();
        } catch (ApplicationNotUseComponentsException $e) {
            return false;
        }

        // Framework fictive "component" for enforcement use of framework controllers
        if ($component === ParsedUrl::FRAMEWORK_COMPONENT) {
            return true;
        }

        // Convert multi-word URL parts to the right form
        $component = $this->stringManipulator->dashesToCamelCase($component);

        if (is_dir($this->configurator->getAppSrcRootDir()."/Controller/".ucfirst($component))) {
            return true;
        } else {
            $parsedUrl->setComponent(ParsedUrl::BAD_VALUE);

            return false;
        }
    }

    /**
     * Verifies that the action is valid for the application and controller
     *
     * @param string|null $action Action for verify
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     *
     * @return bool Is it valid?
     * @noinspection PhpDocMissingThrowsInspection Exception cannot be thrown (controller has been verified yet)
     */
    private function isValidAction(?string $action, ParsedUrl $parsedUrl): bool
    {
        // If action is null or controller is not valid, it doesn't make sense to continue
        if ($action === null || $parsedUrl->getController() === null
            || $parsedUrl->getController() === ParsedUrl::BAD_VALUE) {
            return false;
        }

        // If current request is AJAX one, add suffix to action name
        if ($this->server->isItAjaxRequest() === true) {
            $action .= "-ajax";
        }

        // Convert multi-word URL parts to the right form
        $action = $this->stringManipulator->dashesToCamelCase($action);

        if ($parsedUrl->getComponent() !== ParsedUrl::FRAMEWORK_COMPONENT) {
            // Application controller
            if ($parsedUrl->getComponent() !== null) {
                // Application uses component system
                $fullQualifiedController = ucfirst($this->configurator->getAppRootNamespace())
                    ."\\Controller\\{$parsedUrl->getComponent(true)}\\{$parsedUrl->getController(true)}";
            } else {
                // Application works without components
                $fullQualifiedController = ucfirst($this->configurator->getAppRootNamespace())
                    ."\\Controller\\{$parsedUrl->getController(true)}";
            }
        } else {
            // Framework controller
            $fullQualifiedController = "Mammoth\\Controller\\{$parsedUrl->getController(true)}";
        }

        /**
         * Exception cannot be thrown (controller has been verified yet)
         *
         * @noinspection PhpUnhandledExceptionInspection
         */
        $reflection = new ReflectionClass($fullQualifiedController);

        // getMethod function throws an exception while searching method doesn't exist
        try {
            $reflection->getMethod("{$action}Action");

            return true;
        } catch (ReflectionException $e) {
            $parsedUrl->setAction(ParsedUrl::BAD_VALUE);

            return false;
        }
    }

    /**
     * Verifies that the data part is valid for the application
     *
     * @param string|null $data Data part (one item of data URL params) for verify
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     *
     * @return bool Is it valid?
     */
    private function isValidData(?string $data, ParsedUrl $parsedUrl): bool
    {
        // If controller isn't valid, it doesn't make sense to continue
        if ($parsedUrl->getController() === null || $parsedUrl->getController() === ParsedUrl::BAD_VALUE) {
            return false;
        }

        if (!empty($data) && !filter_input(INPUT_GET, $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Tries to repair language if it's not valid
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     * @param bool $required Is it required URL part?
     *
     * @return string|null Language or null (cannot be repaired)
     */
    private function repairLanguage(ParsedUrl $parsedUrl, bool $required): ?string
    {
        return $this->configurator->getAppDefaultLang();
    }

    /**
     * Tries to repair component if it's not valid
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     * @param bool $required Is it required URL part?
     *
     * @return string|null Component or null (cannot be repaired)
     */
    private function repairComponent(ParsedUrl $parsedUrl, bool $required): ?string
    {
        // Error controller doesn't have component, so although component is "bad",
        // it can be set to null
        if ($parsedUrl->getController() === "error") {
            return null;
        }

        try {
            return $this->configurator->getAppDefaultComponent();
        } catch (ApplicationNotUseComponentsException $e) {
            // Application doesn't use component system
            return null;
        }
    }

    /**
     * Tries to repair controller if it's not valid
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     * @param bool $required Is it required URL part?
     *
     * @return string|null Controller or null (cannot be repaired)
     */
    private function repairController(ParsedUrl $parsedUrl, bool $required): ?string
    {
        // If controller is required, it cannot be repaired
        if ($required === true || $parsedUrl->getController() === ParsedUrl::BAD_VALUE) {
            return ParsedUrl::BAD_VALUE;
        }

        // Try base controller
        $baseController = $this->configurator->getAppBaseControllerName();

        if ($this->isValidController($baseController, $parsedUrl)) {
            return $baseController;
        } else {
            // Application doesn't have implemented base controller yet -> it cannot be used
            return null;
        }
    }

    /**
     * Verifies that the controller is valid for the application and component
     *
     * @param string|null $controller Controller for verify
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     *
     * @return bool Is it valid?
     */
    private function isValidController(?string $controller, ParsedUrl $parsedUrl): bool
    {
        if ($controller === null) {
            return false;
        }

        // Try to use default component from repairComponent() method
        if ($parsedUrl->getComponent() === ParsedUrl::BAD_VALUE) {
            // Required isn't used in repairComponent() method, so given value can be anything
            $parsedUrl->setComponent($this->repairComponent($parsedUrl, true));
        }

        // Convert multi-word URL parts to the right form
        $controller = $this->stringManipulator->dashesToCamelCase($controller);
        $controllerName = ucfirst($controller)."Controller";

        // Namespace
        if ($parsedUrl->getComponent() !== ParsedUrl::FRAMEWORK_COMPONENT) {
            // Application controller
            // Root namespace = Vendor namespace + Namespace for controllers
            $rootNamespace = ucfirst($this->configurator->getAppRootNamespace())."\\Controller";

            // Application use component system
            if (($component = $parsedUrl->getComponent(true)) !== null) {
                $controllerFullQualifiedName = "{$rootNamespace}\\{$component}\\{$controllerName}";
                $controllerPath = $this->configurator->getAppSrcRootDir()."/Controller/{$component}/{$controllerName}.php";
            } else {
                // Application doesn't use it
                $controllerFullQualifiedName = "{$rootNamespace}\\{$controllerName}";
                $controllerPath = $this->configurator->getAppSrcRootDir()."/Controller/{$controllerName}.php";
            }
        } else {
            // Framework controller
            $controllerFullQualifiedName = "Mammoth\\Controller\\{$controllerName}";
            $controllerPath = __DIR__."/../Controller/{$controllerName}.php";
        }

        // Verify that controller file is exists, so it could be required by Loader
        if (!is_file($controllerPath)) {
            $parsedUrl->setController(ParsedUrl::BAD_VALUE);

            return false;
        }

        // Try create instance of Reflection class
        // If the controller class doesn't exist, constructor threw an exception
        try {
            new ReflectionClass($controllerFullQualifiedName);

            return true;
        } catch (ReflectionException $e) {
            $parsedUrl->setController(ParsedUrl::BAD_VALUE);

            return false;
        }
    }

    /**
     * Tries to repair action if it's not valid
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     * @param bool $required Is it required URL part?
     *
     * @return string|null Action or null (cannot be repaired)
     */
    private function repairAction(ParsedUrl $parsedUrl, bool $required): ?string
    {
        // If controller is not valid, it doesn't make sense to continue
        if ($parsedUrl->getController() === null) {
            return null;
        }

        // Set defaultAction as action method
        // This method is obligatory for all controllers
        // (it's abstract method of their parent)
        return "default";
    }

    /**
     * Tries to repair data if it's not valid
     *
     * @param \Mammoth\Url\Entity\ParsedUrl $parsedUrl Parsed URL object (for getting some things)
     * @param bool $required Is it required URL part?
     *
     * @return string|null Data or null (cannot be repaired)
     */
    private function repairData(ParsedUrl $parsedUrl, bool $required): ?string
    {
        // Data cannot be repaired
        return null;
    }
}