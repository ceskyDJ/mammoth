<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Templates\Abstraction;

/**
 * Manager of messages for user (alerts etc.)
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates\Abstraction
 */
interface IMessageManager
{
    /**
     * Returns messages for user
     *
     * @return array|null Messages
     */
    public function getMessages(): ?array;

    /**
     * Adds message for user
     *
     * @param string $text Message text
     * @param string $type Message type (can be used for CSS styling)
     */
    public function addMessage(string $text, string $type = "info"): void;

    /**
     * Deletes all messages for user
     */
    public function dropMessages(): void;
}