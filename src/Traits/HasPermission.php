<?php

namespace Yajra\Acl\Traits;

use Yajra\Acl\Models\Permission;
use App\User;

trait HasPermission
{
    /**
     * Assigns the given permission to the role or user.
     *
     * @param  int $permissionId
     * @return bool
     */
    public function assignPermission($permissionId = null)
    {
        $permissions = $this->permissions;

        if (! $permissions->contains($permissionId)) {
            $this->permissions()->attach($permissionId);

            return true;
        }

        return false;
    }

    /**
     * Get related permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(config('acl.permission', Permission::class))->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(config('acl.user', Permission::class))->withTimestamps();
    }
    /**
     * Revokes the given permission from the role or user.
     *
     * @param  int|null $permissionId
     * @return bool
     */
    public function revokePermission($permissionId = null)
    {
        return $this->permissions()->detach($permissionId);
    }

    /**
     * Syncs the given permission(s) with the role or user.
     *
     * @param  array $permissionIds
     * @return array|bool
     */
    public function syncPermissions(array $permissionIds = [])
    {
        return $this->permissions()->sync($permissionIds);
    }

    /**
     * Revokes all permissions from the role or user.
     *
     * @return bool
     */
    public function revokeAllPermissions()
    {
        return $this->permissions()->detach();
    }

    /**
     * Checks if the role or user has the given permission.
     *
     * @param  string $permission
     * @return bool
     */
    public function can($permission)
    {
        $permissions = $this->getPermissions();

        if (is_array($permission)) {
            $permissionCount   = count($permission);
            $intersection      = array_intersect($permissions, $permission);
            $intersectionCount = count($intersection);

            return ($permissionCount == $intersectionCount) ? true : false;
        } else {
            return in_array($permission, $permissions);
        }
    }

    /**
     * Get list of permissions slug.
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions->pluck('slug')->toArray();
    }

    /**
     * Check if the role or user has at least one of the given permissions.
     *
     * @param  array $permission
     * @return bool
     */
    public function canAtLeast(array $permission = [])
    {
        $permissions = $this->getPermissions();

        $intersection      = array_intersect($permissions, $permission);
        $intersectionCount = count($intersection);

        return ($intersectionCount > 0) ? true : false;
    }
}
