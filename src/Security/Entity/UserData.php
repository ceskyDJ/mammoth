<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Entity;

/**
 * Data item for User entity
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Entity
 */
class UserData
{
    /**
     * @var string Name of the data item
     */
    private string $name;
    /**
     * @var mixed Data item's content
     */
    private $content;

    /**
     * UserData constructor
     *
     * @param string $name
     * @param mixed $content
     */
    public function __construct(string $name, $content)
    {
        $this->name = $name;
        $this->content = $content;
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
     * @return UserData
     */
    public function setName(string $name): UserData
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter for content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Fluent setter for content
     *
     * @param mixed $content
     *
     * @return UserData
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}