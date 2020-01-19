<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Security\Entity;

/**
 * Common permission group for classes that is named permission container (from system view)
 * for ex. Rank is permission container with name for some little stuff.
 * for ex. User is permission container (among other roles) with nick, ID etc.
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Security\Entity
 */
abstract class PermissionGroup
{
    /**
     * @var \Mammoth\Security\Entity\Permission[] Permissions (with allowed state true and false)
     */
    private array $permissions;
    /**
     * @var \Mammoth\Security\Entity\PermissionGroup|null What permission group is pattern to create complete
     *     permission list
     */
    private ?PermissionGroup $permissionPattern;

    /**
     * Getter for permissions
     *
     * @return \Mammoth\Security\Entity\Permission[]
     */
    public function getPermissions(): array
    {
        /**
         * @var $permission \Mammoth\Security\Entity\Permission
         */
        $thisPermissionsKeys = array_map(fn($permission) => $permission->__toString(), $this->permissions);
        /**
         * @var $permission \Mammoth\Security\Entity\Permission
         */
        $patternPermissionsKeys = array_map(
            fn($permission) => $permission->__toString(),
            $this->permissionPattern->getPermissions()
        );

        $thisPermissions = array_combine($thisPermissionsKeys, $this->permissions);
        $patternPermissions = array_combine($patternPermissionsKeys, $this->permissionPattern->getPermissions());

        $resultPermissions = array_merge($patternPermissions, $thisPermissions);

        /**
         * @var $permission \Mammoth\Security\Entity\Permission
         */
        return array_filter($resultPermissions, fn($permission) => $permission->isAllowed());
    }

    /**
     * Fluent setter for permissions
     *
     * @param \Mammoth\Security\Entity\Permission[] $permissions
     *
     * @return PermissionGroup
     */
    public function setPermissions(array $permissions): PermissionGroup
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Adds user specific permission (doesn't change rank)
     *
     * @param \Mammoth\Security\Entity\Permission $permission Permission to add
     */
    public function addPermission(Permission $permission): void
    {
        // If the permission is already in user's permissions,
        // only change its state to allowed
        if ((bool)($key = array_search($permission, $this->permissions))) {
            $this->permissions[$key]->setAllowed(true);

            return;
        }

        // Brand new permission
        $this->permissions[] = $permission;
    }

    /**
     * Delets user specific permission (doesn't change rank)
     *
     * @param \Mammoth\Security\Entity\Permission $permission Permission to delete
     */
    public function deletePermissions(Permission $permission): void
    {
        // If user doesn't have this permission, set it as not allowed
        // This is in case group gives the permission to the user
        if (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission->setAllowed(false);

            return;
        }

        // Permission of the list of the user specific permissions
        unset($this->permissions[array_search($permission, $this->permissions)]);
    }

    /**
     * Getter for permissionPattern
     *
     * @return \Mammoth\Security\Entity\PermissionGroup|null
     */
    protected function getPermissionPattern(): ?PermissionGroup
    {
        return $this->permissionPattern;
    }

    /**
     * Fluent setter for permissionPattern
     *
     * @param \Mammoth\Security\Entity\PermissionGroup|null $permissionPattern
     *
     * @return PermissionGroup
     */
    protected function setPermissionPattern(?PermissionGroup $permissionPattern): PermissionGroup
    {
        $this->permissionPattern = $permissionPattern;

        return $this;
    }
}