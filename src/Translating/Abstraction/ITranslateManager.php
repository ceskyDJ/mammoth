<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Translating\Abstraction;

use Latte\Runtime\FilterInfo;

/**
 * Translate manager
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Abstraction
 */
interface ITranslateManager
{
    /**
     * Translates message for Latte template
     *
     * @param \Latte\Runtime\FilterInfo $info Translate filter runtime info
     * @param mixed $message Message for translating
     *
     * @return string Translated message
     */
    public function translatePageText(FilterInfo $info, $message): string;

    /**
     * Verifies that the lang is valid (allowed for application)
     *
     * @param string $lang Lang for verify
     *
     * @return bool Is it valid lang?
     */
    public function isValidLang(string $lang): bool;
}