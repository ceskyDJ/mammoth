<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Utils;

use function array_filter;
use function array_keys;
use function is_string;
use function mb_convert_case;
use const MB_CASE_LOWER;

/**
 * Helper for working with arrays (superstructure above PHP build in functions)
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Utils
 */
final class ArrayHelper
{
    /**
     * Removes keys without value (or with null value) from array
     *
     * @param array $inputArray Input array
     *
     * @return array Output array
     */
    public function removeEmptyKeys(array $inputArray): array
    {
        return array_filter($inputArray, fn($value) => mb_strlen((string)$value));
    }

    /**
     * Removes items by name from array
     *
     * @param array $inputArray Input array
     * @param mixed $value Value for removing
     * @param bool $strict Strict comparing values
     *
     * @return array Output array
     */
    public function removeByValue(array $inputArray, $value, $strict = false): array
    {
        $keysWithValue = array_keys($inputArray, $value, $strict);

        foreach ($keysWithValue as $key) {
            unset($inputArray[$key]);
        }

        return $inputArray;
    }

    /**
     * Changes case of all values (all chars) in array
     *
     * @param array $inputArray Input array
     * @param int $case Case to change values to
     *
     * @return array Output array
     */
    public function changeValuesCase(array $inputArray, int $case = MB_CASE_LOWER): array
    {
        foreach ($inputArray as &$value) {
            if (is_string($value)) {
                $value = mb_convert_case($value, $case);
            }
        }

        return $inputArray;
    }
}