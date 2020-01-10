<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Reflection;

use Mammoth\Exceptions\NonExistingFileException;
use Mammoth\Reflection\Entity\ReflectionUse;
use Mammoth\Utils\ArrayHelper;
use Mammoth\Utils\FileHelper;
use ReflectionClass;
use ReflectionException;
use function array_column;
use function array_values;
use function count;
use function in_array;
use function preg_match;
use function preg_match_all;

/**
 * Smart reflection class (superstructure above its build in version)
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Reflection
 */
class SmartReflectionClass extends ReflectionClass
{

    /**
     * @var array Array of ReflectionUse's objects
     */
    private array $reflectionUses = [];

    private FileHelper $fileHelper;
    private ArrayHelper $arrayHelper;

    /**
     * SmartReflectionClass construct
     *
     * @param mixed $argument
     * @param \Mammoth\Utils\FileHelper $fileHelper
     * @param \Mammoth\Utils\ArrayHelper $arrayHelper
     *
     * @throws \ReflectionException
     */
    public function __construct($argument, FileHelper $fileHelper, ArrayHelper $arrayHelper)
    {
        parent::__construct($argument);

        $this->fileHelper = $fileHelper;
        $this->arrayHelper = $arrayHelper;

        // Load imports
        $parsedClassFiles = $this->getFiles();
        $usesArray = $this->getUsesFromFile($parsedClassFiles);
        $this->reflectionUses = $this->getUsesAsObjects($usesArray);
    }

    /**
     * Returns file of original class and its ancestors
     *
     * @return array Class files contents (parsed by lines)
     * @throws ReflectionException Invalid class file
     */
    private function getFiles(): array
    {
        try {
            $files[] = $this->fileHelper->parseFile($this->getFileName());

            // Get content of all ancestors
            $class = $this;
            while (($parent = $class->getParentClass())) {
                $files[] = $this->fileHelper->parseFile($parent->getFileName());

                $class = $parent;
            }

            return $files;
        } catch (NonExistingFileException $e) {
            throw new ReflectionException("Class file not found (".$this->getFileName().")", $e->getCode(), $e);
        }
    }

    /**
     * Returns imports (uses) from files
     *
     * @param array $parsedFiles Parsed files (by lines)
     *
     * @return string[] Imports array
     */
    private function getUsesFromFile(array $parsedFiles): array
    {
        $usesStarted = false;
        $uses = [];

        $i = 0;
        foreach ($parsedFiles as $parsedFile) {
            foreach ($parsedFile as $row) {
                // Lines after use section will be omitted
                if (!preg_match("%use ([a-zA-Z0-9\\\_]+)( as )?([a-zA-Z0-9]+)?;%", $row) && $usesStarted) {
                    break;
                }

                // Lines before use section will be skipped
                if (!preg_match("%use ([a-zA-Z0-9\\\_]+)( as )?([a-zA-Z0-9]+)?;%", $row)) {
                    continue;
                }

                $matches = [];
                preg_match_all("%use ([a-zA-Z0-9\\\_]+)( as )?([a-zA-Z0-9]+)?;%", $row, $matches);

                for ($j = 0; $j < count($matches[1]); $j++) {
                    // If the import (use) is in array, skip it
                    if (in_array($matches[1][$j], array_column($uses, 'class'))) {
                        continue;
                    }

                    $uses[$i]['class'] = $matches[1][$j];

                    if (isset($matches[3][$j])) {
                        $uses[$i]['alias'] = $matches[3][$j];
                    }
                }

                $i++;
            }
        }

        // Array re-creation due to the spaces in keys (they're created at skipping of duplicity)
        return array_values($uses);
    }

    /**
     * Returns imports (uses) as objects
     *
     * @param array $usesArray Imports array
     *
     * @return ReflectionUse[] Array of ReflectionUse object
     */
    private function getUsesAsObjects(array $usesArray): array
    {
        $arrayCount = count($usesArray);
        $finalUses = [];

        for ($i = 0; $i < $arrayCount; $i++) {
            $finalUses[$i] = new ReflectionUse($this, $i);
        }

        return $finalUses;
    }

    /**
     * Returns import (use) on the index
     *
     * @param int $index Index in imports array
     *
     * @return ReflectionUse Import (use) on the index
     */
    public function getUse(int $index): ReflectionUse
    {
        return $this->getUses()[$index];
    }

    /**
     * Returns array of imports (uses)
     *
     * @return array Imports (uses)
     */
    public function getUses(): array
    {
        return $this->reflectionUses;
    }

    /**
     * Returns data of the import on the index
     *
     * @param int $index Index of the import
     *
     * @return array|null Data of the import (use)
     * @throws ReflectionException Invalid class file
     */
    public function getUseData(int $index): ?array
    {
        $parsedClassFiles = $this->getFiles();

        $uses = $this->getUsesFromFile($parsedClassFiles);

        return $uses[$index];
    }

    /**
     * Returns import (use) for the class
     * <strong>The class has to be imported in file with the class,
     * which has been passed to this reflection</strong>
     *
     * @param string $class Wanted class
     *
     * @return ReflectionUse Reflection class of import (use)
     * @throws ReflectionException Invalid class file | The class hasn't been imported
     */
    public function getUseForClass(string $class): ReflectionUse
    {
        /**
         * @var $reflectionUse ReflectionUse
         */
        foreach ($this->reflectionUses as $reflectionUse) {
            if ($reflectionUse->getClassName() == $class) {
                return $reflectionUse;
            }
        }

        throw new ReflectionException("The class hasn't been imported");
    }

    /**
     * Decide if the class is a descendant (class) or an implementation (interface) of the class
     * <strong>Verifies only direct descendants</strong>
     *
     * @param string $class Parent class
     *
     * @return bool Is it a descendant or implementation of the class?
     */
    public function isInstanceOf(string $class): bool
    {
        $interfaces = $this->getInterfaceNames();
        $parentClass = $this->getParentClass();

        // Descendant verification
        if ($parentClass && $parentClass->getName() === $class) {
            return true;
        }

        // Implementation verification
        return in_array($class, $interfaces);
    }
}