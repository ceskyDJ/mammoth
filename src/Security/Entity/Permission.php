<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Entity;

/**
 * User's permission
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Entity
 */
class Permission
{
    /**
     * Every actions for specific subjects are allowed
     */
    public const LEVEL_ALL = "all";

    /**
     * @var string What is the permission for
     */
    private string $subject;
    /**
     * @var string Permission level (only read, read and write, all permissions, ...)
     */
    private string $level;
    /**
     * @var bool Is this permission allowed for its owner (user, rank)
     * This is used in inheritance. When I need to remove some permission
     * which is inherited from parent, I'll set this property to false.
     */
    private bool $allowed;

    /**
     * Permission constructor
     *
     * @param string $subject
     * @param string $level
     * @param bool $allowed
     */
    public function __construct(string $subject, string $level = self::LEVEL_ALL, bool $allowed = true)
    {
        $this->subject = $subject;
        $this->level = $level;
        $this->allowed = $allowed;
    }

    /**
     * Getter for subject
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Fluent setter for subject
     *
     * @param string $subject
     *
     * @return Permission
     */
    public function setSubject(string $subject): Permission
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Getter for level
     *
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * Fluent setter for level
     *
     * @param string $level
     *
     * @return Permission
     */
    public function setLevel(string $level): Permission
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Getter for allowed
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    /**
     * Fluent setter for allowed
     *
     * @param bool $allowed
     *
     * @return Permission
     */
    public function setAllowed(bool $allowed): Permission
    {
        $this->allowed = $allowed;

        return $this;
    }

    /**
     * Returns Permission as string
     * The most important is what subject is the permission for,
     * so this method returns only subject
     *
     * @return string Permission object as string (its subject respectively)
     */
    public function __toString(): string
    {
        return $this->subject;
    }
}