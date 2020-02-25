<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Connect\Tracy;

use Mammoth\Security\Abstraction\IUserManager;
use Mammoth\Security\Entity\IRank;
use Mammoth\Security\Entity\UserData;
use Mammoth\Templates\Abstraction\IPrinter;
use Mammoth\Utils\StringManipulator;
use ReflectionClass;
use Tracy\IBarPanel;

/**
 * User panel for Tracy debugger
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Connect\Tracy
 */
class UserPanel implements IBarPanel
{
    /**
     * @inject
     */
    private IUserManager $userManager;
    /**
     * @inject
     */
    private IPrinter $printer;
    /**
     * @inject
     */
    private StringManipulator $stringManipulator;

    /**
     * @inheritDoc
     */
    public function getTab()
    {
        $data = [
            'user' => ($this->userManager->isAnyoneLoggedIn() ? $this->userManager->getUser()->getUserName() : ""),
        ];

        return $this->printer->getFileHTML(__DIR__."/templates/user-panel-tab.latte", $data);
    }

    /**
     * @inheritDoc
     */
    public function getPanel()
    {
        if ($this->userManager->getUser() === null) {
            return null;
        }

        $user = $this->userManager->getUser();
        $rank = $user->getRank();

        // User ID (not required -> display only if not null)
        if (($id = $user->getId()) !== null) {
            $userProperties = [new UserData("ID", $id)];
        } else {
            $userProperties = [];
        }

        // Rank type word
        $rankTypeWord = ($this->getRankTypeAsWord($rank) ?? "");

        $userProperties = [
            ...$userProperties,
            new UserData("nick", $user->getUserName()),
            new UserData("rank", "{$rank->getName()} ({$rankTypeWord})"),
            ...$user->getProperties(),
        ];

        foreach ($userProperties as $property) {
            $property->setName($this->stringManipulator->dashesToHumanReadable($property->getName()));
        }

        /**
         * @var $property \Mammoth\Security\Entity\UserData
         */
        $data = [
            'user'           => $user,
            'userProperties' => $userProperties,
        ];

        return $this->printer->getFileHTML(__DIR__."/templates/user-panel-content.latte", $data);
    }

    /**
     * Returns rank type name
     *
     * @param \Mammoth\Security\Entity\IRank $rank Rank object
     *
     * @return string|null Rank type constant name
     * @noinspection PhpDocMissingThrowsInspection User cannot be without rank
     */
    private function getRankTypeAsWord(IRank $rank): ?string
    {
        /**
         * @noinspection PhpUnhandledExceptionInspection User cannot be without rank
         */
        $rankReflection = new ReflectionClass($rank);
        $rankConstants = $rankReflection->getConstants();

        foreach ($rankConstants as $name => $value) {
            if ($value === $rank->getType()) {
                return mb_strtolower($name);
            }
        }

        return null;
    }
}