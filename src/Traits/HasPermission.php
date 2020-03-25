<?php

namespace Yajra\Acl\Traits;

use Yajra\Acl\Models\Permission;

/**
 * @property \Illuminate\Database\Eloquent\Collection permissions
 */
trait HasPermission
{
    /**
     * Assigns the given permission to the role.
     *
     * @param \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array $ids
     * @param array $attributes
     * @param bool $touch
     * @return void
     */
    public function assignPermission($ids, array $attributes = [], $touch = true)
    {
        $this->permissions()->attach($ids, $attributes, $touch);
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

    /**
     * Revokes the given permission from the role.
     *
     * @param mixed $ids
     * @param bool $touch
     * @return int
     */
    public function revokePermission($ids = null, $touch = true)
    {
        return $this->permissions()->detach($ids, $touch);
    }

    /**
     * Syncs the given permission(s) with the role.
     *
     * @param \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array $ids
     * @param bool $detaching
     * @return array
     */
    public function syncPermissions($ids, $detaching = true)
    {
        return $this->permissions()->sync($ids, $detaching);
    }

    /**
     * Revokes all permissions from the role.
     *
     * @return int
     */
    public function revokeAllPermissions()
    {
        return $this->permissions()->detach();
    }

    /**
     * Checks if the role has the given permission.
     *
     * @param string $permission
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
     * Check if the role has at least one of the given permissions.
     *
     * @param array $permission
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
