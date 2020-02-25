<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Entity;

/**
 * User's rank defines its permissions
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Entity
 */
class Rank extends PermissionGroup implements IRank
{
    /**
     * @var string Name of the rank (only for some print cases etc.)
     */
    private string $name;
    /**
     * @var int User type - user, admin
     */
    private int $type;

    /**
     * Rank constructor
     *
     * @param string $name
     * @param int $type
     * @param array|\Mammoth\Security\Entity\Permission[] $permissions
     * @param \Mammoth\Security\Entity\Rank|null $parent
     */
    public function __construct(string $name, int $type = self::USER, array $permissions = [], ?Rank $parent = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->setPermissions($permissions);
        $this->setPermissionPattern($parent);
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Fluent setter for name
     *
     * @param string $name
     *
     * @return Rank
     */
    public function setName(string $name): Rank
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter for type
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Fluent setter for type
     *
     * @param int $type
     *
     * @return Rank
     */
    public function setType(int $type): Rank
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Getter for parent
     *
     * @return \Mammoth\Security\Entity\Rank|null
     */
    public function getParent(): ?Rank
    {
        /**
         * @var $rank \Mammoth\Security\Entity\Rank
         */
        $rank = $this->getPermissionPattern();

        return $rank;
    }

    /**
     * Fluent setter for parent
     *
     * @param \Mammoth\Security\Entity\Rank|null $parent
     *
     * @return Rank
     */
    public function setParent(?Rank $parent): Rank
    {
        $this->setPermissionPattern($parent);

        return $this;
    }
}