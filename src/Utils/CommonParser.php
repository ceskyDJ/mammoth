<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Utils;

use function explode;
use function is_numeric;
use function mb_convert_case;
use function str_replace;
use function strtolower;
use function trim;
use const MB_CASE_UPPER;

/**
 * Helper for advanced parsing (and converting) to right data types
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Utils
 */
final class CommonParser
{
    /**
     * Convert dashed-separated list to array
     *
     * @param string $commaList String with dashed-separated list
     * @param bool $autoFirstUpper Set first lette of each list item to upper case automatically?
     *
     * @return array List as array
     */
    public function commaStringListToArray(string $commaList, bool $autoFirstUpper = false): array
    {
        $array = explode(",", $commaList);

        $list = [];
        foreach ($array as $item) {
            // Remove lateral spaces
            $item = trim($item);

            // Empty string go to null, normal string get first letter upper case (if it's allowed)
            // If it's not string, the list item will be converted to the right data type
            $item = $this->autoParse($item, $autoFirstUpper);

            // Null items aren't added to result array
            if ($item !== null) {
                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * Converts string to the right data type automatically (include treatment)
     * <strong>Support:</strong>
     * <ul>
     *     <li>string
     *     <li>int
     *     <li>float
     *     <li>bool (only in forms: "true" and "false" - case insensitive)
     * </ul>
     *
     * @param string $string Input string
     * @param bool $autoFirstUpper Set first letter upper case automatically? (only for strings!)
     *
     * @return mixed Element with right data type
     */
    public function autoParse(string $string, bool $autoFirstUpper = false)
    {
        // Boolean
        if (strtolower($string) === "true") {
            return true;
        } elseif (strtolower($string) === "false") {
            return false;
        }

        // Text (string)
        if (!$this->isNumeric($string)) {
            return $this->fixString($string, $autoFirstUpper);
        }

        if ((string)$this->parseInt($string) === $string) {
            // Integer
            // (there isn't decimal part at float after converting to integer)
            return $this->parseInt($string);
        } else {
            // Float
            return $this->parseFloat($string);
        }
    }

    /**
     * Checks that there's a numeric value in the string
     *
     * @param string $string Input string
     *
     * @return bool Is the string value numeric?
     */
    public function isNumeric(string $string): bool
    {
        return is_numeric(str_replace(",", ".", $string));
    }

    /**
     * Repairs the string (replaces empty string with null and sets first letter upper case)
     *
     * @param string $string Input string
     * @param bool $autoFirstUpper Set first letter upper case automatically?
     *
     * @return string|null Repaired string or null
     */
    public function fixString(string $string, bool $autoFirstUpper = false): ?string
    {
        if ($autoFirstUpper === true) {
            $firstUpper = mb_convert_case(mb_substr($string, 0, 1), MB_CASE_UPPER);
            $string = $firstUpper.mb_substr($string, 1);
        }

        return $string === "" ? null : $string;
    }

    /**
     * Converts string to integer
     *
     * @param string $string Integer as a string
     *
     * @return int|null Integer or null
     */
    public function parseInt(string $string): ?int
    {
        if ($string === "") {
            return null;
        }

        return (int)str_replace(",", ".", $string);
    }

    /**
     * Converts string to float
     *
     * @param string $string Float as a string
     *
     * @return float|null Float or null
     */
    public function parseFloat(string $string): ?float
    {
        if ($string === "") {
            return null;
        }

        return (float)str_replace(",", ".", $string);
    }
}