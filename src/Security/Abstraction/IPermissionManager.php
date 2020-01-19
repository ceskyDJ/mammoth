<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Abstraction;

use Mammoth\Security\Entity\Permission;

/**
 * Manager of user permissions
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Abstraction
 */
interface IPermissionManager
{
    /**
     * Verifies that current user (but can be visitor, too) has some permission
     *
     * @param string $subject Permission's subject
     * @param string $level Permission's level
     *
     * @return bool Has the user the permission?
     */
    public function verifyPermission(string $subject, string $level = Permission::LEVEL_ALL): bool;

    /**
     * Verifies that current user has permission to view specific component
     *
     * @param string $component Component name
     *
     * @return bool Has current user access?
     */
    public function verifyAccessToComponent(string $component): bool;
}