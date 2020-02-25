<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Entity;

/**
 * Interface for user
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Entity
 */
interface IUser
{
    /**
     * Returns user's identification
     *
     * @return string|null User's identification
     */
    public function getId(): ?string;

    /**
     * Returns user's username (login name)
     *
     * @return string User's username - nick, email etc.
     */
    public function getUserName(): string;

    /**
     * Returns all user's properties
     *
     * @return \Mammoth\Security\Entity\UserData[] All properties
     */
    public function getProperties(): array;

    /**
     * Returns individual property (if exists)
     *
     * @param string $name Name of data item to find
     *
     * @return \Mammoth\Security\Entity\UserData|null User data object (property) or null if not found
     */
    public function getProperty(string $name): ?UserData;

    /**
     * Checks that this is object of logged in user
     *
     * @return bool Is it logged in user or only visitor?
     */
    public function isLoggedIn(): bool;

    /**
     * Returns user's rank
     *
     * @return \Mammoth\Security\Entity\IRank User's rank
     */
    public function getRank(): IRank;
}