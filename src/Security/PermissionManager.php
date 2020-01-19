<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security;

use Mammoth\Common\DIClass;
use Mammoth\Exceptions\NotPartOfFrameworkException;
use Mammoth\Security\Abstraction\IPermissionManager;
use Mammoth\Security\Entity\Permission;

/**
 * Správce uživatelských oprávnění
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Templates
 */
class PermissionManager implements IPermissionManager
{
    use DIClass;

    /**
     * @inheritDoc
     * @throws \Mammoth\Exceptions\NotPartOfFrameworkException Not part of framework
     */
    public function verifyPermission(string $subject, string $level = Permission::LEVEL_ALL): bool
    {
        throw new NotPartOfFrameworkException("This feature has to be implemented by application");
    }

    /**
     * @inheritDoc
     */
    public function verifyAccessToComponent(string $component): bool
    {
        // There is no way to control this by framework.
        // If application needs this feature, it has to implement it itself
        return true;
    }
}
