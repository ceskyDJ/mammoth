<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Utils;

use function implode;
use function lcfirst;
use function preg_split;
use function str_replace;
use function ucwords;

/**
 * Helper for working with strings
 *
 * @author Michal ŠMAHEL (ceskyDJ)
 * @package Mammoth\Utils
 */
final class StringManipulator
{
    /**
     * Converts string with words separated by dashes (-) to camel case string
     *
     * @param $text string Input string (with dashes)
     *
     * @return string Output string (camel case) - ex. exampleTextString
     */
    public function dashesToCamelCase(string $text): string
    {
        $sentence = str_replace('-', ' ', $text);
        $sentence = ucwords($sentence);

        return lcfirst(str_replace(' ', '', $sentence));
    }

    /**
     * Converts camel case string to string with words separated by dashes (-)
     *
     * @param string $text Input string (camel case)
     *
     * @return string Output string (with dashes) - ex. example-text-string
     */
    public function camelCaseToDashes(string $text): string
    {
        $asArray = preg_split("%(?=[A-Z])%", $text);

        return mb_strtolower(implode("-", $asArray));
    }
}