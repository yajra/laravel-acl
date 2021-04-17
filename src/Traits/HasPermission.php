<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\Acl\Models\Permission;

/**
 * @property \Illuminate\Database\Eloquent\Collection permissions
 */
trait HasPermission
{
    use InteractsWithPermission;

    /**
     * Checks if the role has the given permission.
     *
     * @param  array|string  $permission
     * @return bool
     */
    public function can($permission): bool
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
     * @param  array  $permission
     * @return bool
     */
    public function canAtLeast(array $permission = []): bool
    {
        $permissions = $this->getPermissions();

        $intersection = array_intersect($permissions, $permission);
        $intersectionCount = count($intersection);

        return $intersectionCount > 0;
    }
}
