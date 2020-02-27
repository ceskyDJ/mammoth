<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security;

use Mammoth\DI\DIClass;
use Mammoth\Exceptions\NonExistingKeyException;
use Mammoth\Http\Entity\Session;
use Mammoth\Security\Abstraction\IUserManager;
use Mammoth\Security\Entity\IRank;
use Mammoth\Security\Entity\IUser;
use Mammoth\Security\Entity\Rank;
use Mammoth\Security\Entity\User;

/**
 * User manager
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security
 */
class UserManager implements IUserManager
{
    use DIClass;

    /**
     * @inject
     */
    private Session $session;

    /**
     * @var \Mammoth\Security\Entity\IUser|null Current user
     */
    protected ?IUser $user = null;

    /**
     * @inheritDoc
     */
    public function getUser(): ?IUser
    {
        return $this->user;
    }

    /**
     * @inheritDoc
     */
    public function isAnyoneLoggedIn(): bool
    {
        return $this->user !== null ? $this->user->isLoggedIn() : false;
    }

    /**
     * @inheritDoc
     */
    public function logInUserAutomatically(): void
    {
        // Mammoth can't log user automatically at this time, so you must do it yourself
        $this->logInUserToSystem($this->createVisitor());
    }

    /**
     * @inheritDoc
     */
    public function logInUserToSystem(IUser $user, bool $permanent = false): void
    {
        $this->user = $user;

        if ($permanent === true) {
            $this->session->setSessionItem("user", $user);
        }
    }

    /**
     * Creates visitor (not logged in user)
     *
     * @return \Mammoth\Security\Entity\IUser Visitor
     */
    protected function createVisitor(): IUser
    {
        return new User(null, "Visitor", new Rank("Visitor", IRank::VISITOR));
    }

    /**
     * @inheritDoc
     */
    public function logOutUserFromSystem(): void
    {
        $this->logInUserToSystem($this->createVisitor());

        $this->session->deleteSessionItem("user");
    }
}