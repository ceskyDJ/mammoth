<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\DI;

use Mammoth\Config\Configurator;
use Mammoth\Exceptions\LoadNonInjectableClassException;
use Mammoth\Reflection\SmartReflectionClass;
use Mammoth\Utils\ArrayHelper;
use Mammoth\Utils\FileHelper;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use function array_key_exists;
use function end;
use function explode;
use function get_class;
use function interface_exists;
use function ltrim;
use function preg_match;
use function strstr;
use function strtolower;

/**
 * Dependency injection container
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\DI
 */
class DIContainer
{
    private Configurator $configurator;
    private FileHelper $fileHelper;
    private ArrayHelper $arrayHelper;

    /**
     * @var array Loaded class instances
     */
    private array $loadedInstances = [];

    /**
     * DIContainer constructor
     *
     * @param \Mammoth\Config\Configurator $configurator
     */
    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;

        // Helpers for Reflection class
        // Cannot be creating by the normal way, because Reflection class
        // is always required for creating instance by DI basic system
        $this->fileHelper = new FileHelper;
        $this->arrayHelper = new ArrayHelper;

        $this->addInstance($this->fileHelper);
        $this->addInstance($this->arrayHelper);
    }

    /**
     * Adds new class instance
     * It's used to adding instances with parametric constructors
     *
     * @param object $instance Instance for inserting
     *
     * @return DIContainer Own instance (method chaining)
     */
    public function addInstance(object $instance): self
    {
        $key = get_class($instance);

        $this->loadedInstances[$key] = $instance;

        return $this;
    }

    /**
     * Returns class instance
     *
     * @param string $classWithNamespace Full qualified class name (with namespaces)
     *
     * @return object Class instance (object)
     * @throws ReflectionException Invalid class
     * @throws LoadNonInjectableClassException Loading class that implements NonInjectable
     */
    public function getInstance(string $classWithNamespace): ?object
    {
        // Unification of full qualified class name styles
        // first: \Vendor\Namespace\Class
        // second: Vendor\Namespace\Class (without \ at first position)
        $classWithNamespace = ltrim($classWithNamespace, "\\");

        // Save original class name (fully qualified, of course) from parameter with only cosmetic edits
        // This is because instead of interfaces are injected implementation classes and $classWithNamespace
        // is changed to implementation class name. This is fine for creating its instance
        // but not for storing it (system is searching for interface name not implement class one - this is
        // a task of this DI container)
        $originalClassWithNamespace = $classWithNamespace;

        // Instance has been created yet
        if (array_key_exists($originalClassWithNamespace, $this->loadedInstances)
            && $this->loadedInstances[$originalClassWithNamespace] != null) {
            return $this->loadedInstances[$originalClassWithNamespace];
        }

        // Instance cannot be created
        $reflection = new SmartReflectionClass($classWithNamespace, $this->fileHelper, $this->arrayHelper);
        if ($reflection->isInstanceOf(NonInjectable::class) && !$reflection->isInstanceOf(SpecialInjectable::class)) {
            throw new LoadNonInjectableClassException("Class {$classWithNamespace} cannot be auto-injected");
        }

        // Replace interface for implement class (if needed)
        if (interface_exists($classWithNamespace)) {
            $interfaceFullQualifiedNameArray = explode("\\", $classWithNamespace);
            $interfaceShortName = end($interfaceFullQualifiedNameArray);
            $classWithNamespace = $this->configurator->getImplementClassFromInterface($interfaceShortName);
        }

        // Create instance and inject dependencies
        /**
         * Only in cases of class with manually-injectable property(properties)
         *
         * @var $instance SpecialInjectable Class with manually-injectable property(properties)
         */
        $instance = new $classWithNamespace();

        // Normal class
        if (!$reflection->isInstanceOf(SpecialInjectable::class)) {
            $this->injectDependencies($instance);
        } else {
            $instance->inject($this);
        }

        return $this->loadedInstances[$originalClassWithNamespace] = $instance;
    }

    /**
     * Injects dependencies into instance
     *
     * @param object $instance Instance without injected dependencies
     *
     * @throws ReflectionException Invalid class
     * @throws LoadNonInjectableClassException Not injectable class
     */
    public function injectDependencies(object $instance): void
    {
        // Get properties (class parameters) from instance by reflection
        $reflection = new ReflectionObject($instance);
        $properties = $reflection->getProperties();

        // Parameter iteration and dependency injection
        /**
         * @var $property ReflectionProperty Class property
         */
        foreach ($properties as $property) {
            // Skip parameter, which doesn't require dependency injection
            if (!strstr(strtolower($docComment = $property->getDocComment()), "@inject")) {
                continue;
            }

            if ($property->getType() !== null) {
                $fullClassName = $this->getPropertyTypeAsFullClassName($property);
            } else {
                // Old way
                // PHP 7.4 adds property static types, use them
                $fullClassName = $this->getPropertyTypeFromDocComment($property, $instance);
            }

            // Instance injection into parameter
            $propertyInstance = $this->getInstance($fullClassName);

            // Allow access temporary for setting value via reflection (like "setter")
            $property->setAccessible(true);

            $property->setValue($instance, $propertyInstance);
        }
    }

    /**
     * Return property's type as class normally from its PHP 7.4+ static type
     *
     * @param \ReflectionProperty $property Property reflection instance
     *
     * @return string Property's type as full qualified class name
     */
    private function getPropertyTypeAsFullClassName(ReflectionProperty $property): string
    {
        /**
         * PHP Storm has class ReflectionType as return type of getType() method
         * but it returns ReflectionNamedType instance
         *
         * @var $propertyType \ReflectionNamedType
         */
        $propertyType = $property->getType();

        return $propertyType->getName();
    }

    /**
     * Returns property's type (as class) from its doc comment
     *
     * @param \ReflectionProperty $property Property reflection instance
     * @param object $instance Instance of class that need to find dependencies
     *
     * @return string Property's type as full qualified class name
     * @throws \ReflectionException Invalid class
     */
    private function getPropertyTypeFromDocComment(ReflectionProperty $property, object $instance): string
    {
        // Extract class name from doc comment
        $matches = [];
        preg_match("%@var ([a-zA-Z0-9\\\_]+)%", $property->getDocComment(), $matches);

        [, $className] = $matches;

        // Get full qualified class name
        if (strstr($className, "\\")) {
            // Class name is full qualified type -> everything is OK
            return $className;
        } else {
            // It's the short one, so it's to transform to full qualified one
            // Find full qualified class name (with namespaces)
            $reflectionClass = new SmartReflectionClass($instance, $this->fileHelper, $this->arrayHelper);

            try {
                $reflectionUse = $reflectionClass->getUseForClass($className);

                return $reflectionUse->getFullClassName();
            } catch (ReflectionException $e) {
                // Dependency class is in the same namespaces like actual instance's class
                // (=> there isn't any import (use) in the file for required dependency)
                // Namespaces are inherited from actual instance
                return $reflectionClass->getNamespaceName()."\\$className";
            }
        }
    }
}