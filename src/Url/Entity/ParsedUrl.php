<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Url\Entity;

use Mammoth\Exceptions\NotCamelCaseSyntaxException;
use function is_array;
use function preg_match;
use function str_replace;
use function ucfirst;

/**
 * Entity for storing and operating with parsed URL
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Http\Entity
 */
final class ParsedUrl
{

    /**
     * Component for permit using framework controllers
     */
    public const FRAMEWORK_COMPONENT = "mammoth";

    /**
     * @var string[] Array of "clean" routes (without %, [ and ])
     */
    private array $routeArray;
    /**
     * @var string|null Language
     */
    private ?string $language;
    /**
     * @var string|null Component
     */
    private ?string $component;
    /**
     * @var string|null Controller
     */
    private ?string $controller;
    /**
     * @var string|null Action
     */
    private ?string $action;
    /**
     * @var array|null Data
     */
    private ?array $data;

    /**
     * ParsedUrl constructor
     *
     * @param string[] $route
     * @param string|null $language
     * @param string|null $component
     * @param string|null $controller
     * @param string|null $action
     * @param array|null $data
     */
    public function __construct(
        array $route,
        ?string $language = null,
        ?string $component = null,
        ?string $controller = null,
        ?string $action = null,
        ?array $data = null
    ) {
        $this->routeArray = str_replace(["%", "[", "]"], "", $route);
        $this->language = $language;
        $this->component = $component;
        $this->controller = $controller;
        $this->action = ($action ?: "default");
        $this->data = $data;
    }

    /**
     * Getter for routeArray
     *
     * @return string[]
     */
    public function getRouteArray(): array
    {
        return $this->routeArray;
    }

    /**
     * Fluent setter for routeArray
     *
     * @param string[] $routeArray
     *
     * @return ParsedUrl
     */
    public function setRouteArray(array $routeArray): ParsedUrl
    {
        $this->routeArray = $routeArray;

        return $this;
    }

    /**
     * Getter for language
     *
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Fluent setter for language
     *
     * @param string|null $language
     *
     * @return ParsedUrl
     */
    public function setLanguage(?string $language): ParsedUrl
    {
        if ($this->validCamelCase($language) === false) {
            throw new NotCamelCaseSyntaxException("Language has to use camel case");
        }

        $this->language = $language;

        return $this;
    }

    /**
     * Validates camel case syntax of the string
     *
     * @param string|null $string String for verify
     *
     * @return bool Is the string OK?
     */
    private function validCamelCase(?string $string): bool
    {
        // It can't be bad because there is nothing
        if ($string === null) {
            return true;
        }

        return (bool)preg_match("%^[a-zA-Z0-9]+$%", $string);
    }

    /**
     * Getter for component
     *
     * @param bool $asNamespace Return in namespace type (with first char upper case)?
     *
     * @return string|null
     */
    public function getComponent(bool $asNamespace = false): ?string
    {
        return ($this->component !== null ? ($asNamespace === true ? ucfirst($this->component) : $this->component)
            : null);
    }

    /**
     * Fluent setter for component
     *
     * @param string|null $component
     *
     * @return ParsedUrl
     */
    public function setComponent(?string $component): ParsedUrl
    {
        if ($this->validCamelCase($component) === false) {
            throw new NotCamelCaseSyntaxException("Component has to use camel case");
        }

        $this->component = $component;

        return $this;
    }

    /**
     * Getter for controller
     *
     * @param bool $appendSuffix Append "Controller" suffix (it's form that is used in code)
     *
     * @return string|null
     */
    public function getController(bool $appendSuffix = false): ?string
    {
        return ($this->controller !== null ? ($appendSuffix === true ? ucfirst($this->controller."Controller")
            : $this->controller) : null);
    }

    /**
     * Fluent setter for controller
     *
     * @param string|null $controller
     *
     * @return ParsedUrl
     */
    public function setController(?string $controller): ParsedUrl
    {
        if ($this->validCamelCase($controller) === false) {
            throw new NotCamelCaseSyntaxException("Controller has to use camel case");
        }

        $this->controller = $controller;

        return $this;
    }

    /**
     * Getter for action
     *
     * @param bool $appendSuffix Append "Action" suffix (it's form that is used in code)
     *
     * @return string|null
     */
    public function getAction(bool $appendSuffix = false): ?string
    {
        return ($appendSuffix === true ? $this->action."Action" : $this->action);
    }

    /**
     * Fluent setter for action
     *
     * @param string|null $action
     *
     * @return ParsedUrl
     */
    public function setAction(?string $action): ParsedUrl
    {
        if ($this->validCamelCase($action) === false) {
            throw new NotCamelCaseSyntaxException("Action has to use camel case");
        }

        $this->action = $action;

        return $this;
    }

    /**
     * Getter for data
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Fluent setter for data
     *
     * @param array|null $data
     *
     * @return ParsedUrl
     */
    public function setData(?array $data): ParsedUrl
    {
        if ($data !== null) {
            foreach ($data as $item) {
                if ($this->validCamelCase($item) === false) {
                    throw new NotCamelCaseSyntaxException("All data parts have to use camel case");
                }
            }
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Adds data part to data array
     *
     * @param mixed|null $dataPart Data part (param in URL)
     *
     * @return ParsedUrl
     */
    public function addData($dataPart): ParsedUrl
    {
        // Create array in $this->data if it's been created yet
        if (!is_array($this->data)) {
            $this->data = [];
        }

        $this->data[] = $dataPart;

        return $this;
    }
}