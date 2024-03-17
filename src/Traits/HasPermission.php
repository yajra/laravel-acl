<?php

namespace Yajra\Acl\Traits;

trait HasPermission
{
    use InteractsWithPermission;

    /**
     * Checks if the role does not have the given permission.
     *
     * @param  string|string[]  $permission
     */
    public function cannot(array|string $permission): bool
    {
        return ! $this->can($permission);
    }

    /**
     * Checks if the role has the given permission.
     *
     * @param  string|string[]  $permission
     */
    public function can(array|string $permission): bool
    {
        $permissions = $this->getPermissions();

        if (is_array($permission)) {
            $permissionCount = count($permission);
            $intersection = array_intersect($permissions, $permission);
            $intersectionCount = count($intersection);

            return $permissionCount == $intersectionCount;
        }

        return in_array($permission, $permissions);
    }

    /**
     * Check if the role has at least one of the given permissions.
     *
     * @param  string|string[]  $permission
     */
    public function canAtLeast(string|array $permission): bool
    {
        $permissions = $this->getPermissions();

        $intersection = array_intersect($permissions, (array) $permission);
        $intersectionCount = count($intersection);

        return $intersectionCount > 0;
    }
}
