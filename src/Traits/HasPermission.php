<?php

namespace Yajra\Acl\Traits;

use Yajra\Acl\Models\Permission;

trait HasPermission
{
    /**
     * Assigns the given permission to the role.
     *
     * @param  int $permissionId
     * @return bool
     */
    public function assignPermission($permissionId = null)
    {
        $permissions = $this->permissions;

        if (! $permissions->contains($permissionId)) {
            return $this->permissions()->attach($permissionId);
        }

        return false;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(config('acl.permission', Permission::class))->withTimestamps();
    }

    /**
     * Revokes the given permission from the role.
     *
     * @param  int|null $permissionId
     * @return bool
     */
    public function revokePermission($permissionId = null)
    {
        return $this->permissions()->detach($permissionId);
    }

    /**
     * Syncs the given permission(s) with the role.
     *
     * @param  array $permissionIds
     * @return bool
     */
    public function syncPermissions(array $permissionIds = [])
    {
        return $this->permissions()->sync($permissionIds);
    }

    /**
     * Revokes all permissions from the role.
     *
     * @return bool
     */
    public function revokeAllPermissions()
    {
        return $this->permissions()->detach();
    }

    /**
     * Check if the role has at least one of the given permissions
     *
     * @param  array $permission
     * @return bool
     */
    public function canAtLeast(array $permission = [])
    {
        $permissions = $this->getPermissionSlugs();

        $intersection      = array_intersect($permissions, $permission);
        $intersectionCount = count($intersection);

        return ($intersectionCount > 0) ? true : false;
    }

    /**
     * Get list of permissions slug.
     *
     * @return mixed
     */
    public function getPermissionSlugs()
    {
        return $this->permissions->pluck('slug')->toArray();
    }
}
