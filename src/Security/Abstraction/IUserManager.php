<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Abstraction;

use Mammoth\Security\Entity\IUser;

/**
 * User manager
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Abstraction
 */
interface IUserManager
{
    /**
     * Returns current user
     * It haven't to be user directly but visitor, too
     *
     * @return \Mammoth\Security\Entity\IUser|null User active user (request sender) or null if no user is in system
     */
    public function getUser(): ?IUser;

    /**
     * Checks if someone is logged in
     *
     * @return bool Is some user logged in?
     */
    public function isAnyoneLoggedIn(): bool;

    /**
     * Automatically logs in user or visitor (not classic logging in, of course)
     */
    public function logInUserAutomatically(): void;

    /**
     * Logs in user to system
     * After that system can do many things with this user
     *
     * @param \Mammoth\Security\Entity\IUser $user User object
     * @param bool $permanent Permanent login (respectively login for a longer time)
     */
    public function logInUserToSystem(IUser $user, bool $permanent = false): void;

    /**
     * Logs out user from system (respectively converts it to visitor)
     */
    public function logOutUserFromSystem(): void;
}