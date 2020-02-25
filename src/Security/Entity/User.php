<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Entity;

use Mammoth\Exceptions\InvalidUserPropertiesArrayException;
use function array_search;

/**
 * User (logged in) actually using application
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Entity
 */
class User extends PermissionGroup implements IUser
{
    /**
     * @var string|null User's identification (can be any type of ID -> string as type)
     */
    private ?string $id;
    /**
     * @var string Nickname
     */
    private string $nick;
    /**
     * @var \Mammoth\Security\Entity\UserData[] Other user's properties (first name, last name, date of birth etc.)
     */
    private array $properties;

    /**
     * User constructor
     *
     * @param string|null $id
     * @param string $nick
     * @param \Mammoth\Security\Entity\Rank $rank
     * @param \Mammoth\Security\Entity\Permission[] $permissions
     * @param \Mammoth\Security\Entity\UserData[] $properties
     */
    public function __construct(
        ?string $id,
        string $nick,
        Rank $rank,
        array $permissions = [],
        array $properties = []
    ) {
        $this->validPropertiesArray($properties);

        $this->id = $id ?? null;
        $this->nick = $nick;
        $this->setPermissionPattern($rank);
        $this->setPermissions($permissions);
        $this->properties = $properties;
    }

    /**
     * Validates input properties array (all members have to be instances of UserData class)
     *
     * @param array $properties Array of input properties
     *
     * @throws \Mammoth\Exceptions\InvalidUserPropertiesArrayException One or more members aren't OK
     */
    private function validPropertiesArray(array $properties): void
    {
        foreach ($properties as $property) {
            if (!($property instanceof UserData)) {
                throw new InvalidUserPropertiesArrayException(
                    "Every member of user properties array has to be UserData object"
                );
            }
        }
    }

    /**
     * Getter for id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Fluent setter for id
     *
     * @param string|null $id
     *
     * @return User
     */
    public function setId(?string $id): User
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Getter for nick
     *
     * @return string
     */
    public function getNick(): string
    {
        return $this->nick;
    }

    /**
     * Fluent setter for nick
     *
     * @param string $nick
     *
     * @return User
     */
    public function setNick(string $nick): User
    {
        $this->nick = $nick;

        return $this;
    }

    /**
     * Fluent setter for rank
     *
     * @param \Mammoth\Security\Entity\Rank $rank
     *
     * @return User
     */
    public function setRank(Rank $rank): User
    {
        $this->setPermissionPattern($rank);

        return $this;
    }

    /**
     * Getter for properties
     *
     * @return \Mammoth\Security\Entity\UserData[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Fluent setter for properties
     *
     * @param \Mammoth\Security\Entity\UserData[] $properties
     *
     * @return User
     */
    public function setProperties(array $properties): User
    {
        $this->validPropertiesArray($properties);

        $this->properties = $properties;

        return $this;
    }

    /**
     * Adds new property to user
     *
     * @param \Mammoth\Security\Entity\UserData $property New property
     */
    public function addProperty(UserData $property): void
    {
        $this->properties[] = $property;
    }

    /**
     * Deletes property
     *
     * @param string|\Mammoth\Security\Entity\UserData $property Property name or its object
     */
    public function deleteProperty($property): void
    {
        // Find property object by its name
        if (!($property instanceof UserData)) {
            $property = $this->getProperty($property);
        }

        // If the property is in array, delete it, otherwise there is nothing to do
        if (($key = array_search($property, $this->properties)) === false) {
            unset($this->properties[$key]);
        }
    }

    /**
     * Return individual property (if exists)
     *
     * @param string $name Name of data item to find
     *
     * @return \Mammoth\Security\Entity\UserData|null User data object (property) or null if not found
     */
    public function getProperty(string $name): ?UserData
    {
        foreach ($this->properties as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }

        return null;
    }

    /**
     * Checks that this is object of logged in user
     *
     * @return bool Is it logged in user or only visitor?
     */
    public function isLoggedIn(): bool
    {
        return ($this->getRank()->getType() !== IRank::VISITOR);
    }

    /**
     * Getter for rank
     *
     * @return \Mammoth\Security\Entity\Rank
     */
    public function getRank(): IRank
    {
        /**
         * @var $rank \Mammoth\Security\Entity\Rank
         */
        $rank = $this->getPermissionPattern();

        return $rank;
    }

    /**
     * @inheritDoc
     */
    public function getUserName(): string
    {
        return $this->getNick();
    }
}