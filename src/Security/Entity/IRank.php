<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Entity;

/**
 * Interface for user rank
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Entity
 */
interface IRank
{
    /**
     * Not logged in user
     */
    public const VISITOR = 0;
    /**
     * Basic user
     */
    public const USER = 1;
    /**
     * Admin with all permissions
     */
    public const ADMIN = 2;

    /**
     * Returns rank name
     *
     * @return string Rank name
     */
    public function getName(): string;

    /**
     * Returns rank type
     *
     * @return int Rank type(IRank::VISITOR, IRank::USER, IRank::ADMIN)
     */
    public function getType(): int;
}