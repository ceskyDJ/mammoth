<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Url;

use Mammoth\Common\DIClass;
use Mammoth\Config\Configurator;
use Mammoth\Exceptions\ApplicationNotUseComponentsException;
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
use function explode;
use function filter_input;
use function implode;
use function is_array;
use function is_dir;
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
            if ($parsedUrl->$getMethod() === null) {
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

        return $this->translateManager->isValidLang($lang);
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

        // Framework fictive "component" for enforcement use of framework controllers
        if ($component === ParsedUrl::FRAMEWORK_COMPONENT) {
            return true;
        }

        // Convert multi-word URL parts to the right form
        $component = $this->stringManipulator->dashesToCamelCase($component);

        return is_dir($this->configurator->getAppSrcRootDir()."/Controller/".ucfirst($component));
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
        if ($action === null || $parsedUrl->getController() === null) {
            return false;
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
        if ($parsedUrl->getController() === null) {
            return false;
        }

        return !empty($data) && !filter_input(INPUT_GET, $data);
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
        if ($required === true) {
            return null;
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
            } else {
                // Application doesn't use it
                $controllerFullQualifiedName = "{$rootNamespace}\\{$controllerName}";
            }
        } else {
            // Framework controller
            $controllerFullQualifiedName = "Mammoth\\Controller\\{$controllerName}";
        }

        // Try create instance of Reflection class
        // If the controller class doesn't exist, constructor threw an exception
        try {
            new ReflectionClass($controllerFullQualifiedName);

            return true;
        } catch (ReflectionException $e) {
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