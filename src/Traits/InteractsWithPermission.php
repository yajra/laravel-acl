<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\Acl\Models\Permission;

/**
 * @property \Illuminate\Database\Eloquent\Collection|Permission[] permissions
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait InteractsWithPermission
{
    private $permissionClass;

    /**
     * Grant permissions by slug(/s).
     *
     * @param  string|array  $slug
     */
    public function grantPermissionBySlug($slug)
    {
        $this->getPermissionClass()
            ->newQuery()
            ->whereIn('slug', (array) $slug)
            ->each(function ($permission) {
                $this->grantPermission($permission);
            });

        $this->load('permissions');
    }

    /**
     * Get Permission class.
     *
     * @return \Yajra\Acl\Models\Permission
     */
    public function getPermissionClass(): Permission
    {
        if (!isset($this->permissionClass)) {
            $this->permissionClass = resolve(config('acl.permission'));
        }

        return $this->permissionClass;
    }

    /**
     * Get related permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(config('acl.permission', Permission::class))->withTimestamps();
    }

    /**
     * Grant permissions by resource.
     *
     * @param  string|array  $resource
     */
    public function grantPermissionByResource($resource)
    {
        $this->getPermissionClass()
            ->newQuery()
            ->whereIn('resource', (array) $resource)
            ->each(function ($permission) {
                $this->grantPermission($permission);
            });

        $this->load('permissions');
    }

    /**
     * Grant the given permission.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array  $id
     * @param  array  $attributes
     * @param  bool  $touch
     * @return void
     */
    public function grantPermission($id, array $attributes = [], $touch = true)
    {
        $this->permissions()->attach($id, $attributes, $touch);

        $this->load('permissions');
    }

    /**
     * Revoke permissions by the given slug(/s).
     *
     * @param  string|array  $slug
     */
    public function revokePermissionBySlug($slug)
    {
        $this->getPermissionClass()
            ->newQuery()
            ->whereIn('slug', (array) $slug)
            ->each(function ($permission) {
                $this->revokePermission($permission);
            });

        $this->load('permissions');
    }

    /**
     * Revoke permissions by resource.
     *
     * @param string|array $resource
     */
    public function revokePermissionByResource($resource)
    {
        $this->getPermissionClass()
            ->newQuery()
            ->whereIn('resource', (array) $resource)
            ->each(function ($permission) {
                $this->revokePermission($permission);
            });

        $this->load('permissions');
    }

    /**
     * Revokes the given permission.
     *
     * @param  mixed  $id
     * @param  bool  $touch
     * @return int
     */
    public function revokePermission($id = null, $touch = true): int
    {
        $detached = $this->permissions()->detach($id, $touch);

        $this->load('permissions');

        return  $detached;
    }

    /**
     * Syncs the given permission.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array  $ids
     * @param  bool  $detaching
     * @return array
     */
    public function syncPermissions($ids, $detaching = true): array
    {
        $synced = $this->permissions()->sync($ids, $detaching);

        $this->load('permissions');

        return $synced;
    }

    /**
     * Revokes all permissions.
     *
     * @return int
     */
    public function revokeAllPermissions(): int
    {
        $detached = $this->permissions()->detach();

        $this->load('permissions');

        return $detached;
    }

    /**
     * Get list of permissions slug.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions->pluck('slug')->toArray();
    }
}
