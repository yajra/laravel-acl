<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\Acl\Models\Permission;

/**
 * @property \Illuminate\Database\Eloquent\Collection|Permission[] $permissions
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait InteractsWithPermission
{
    /**
     * @var class-string|Permission
     */
    public $permissionClass;

    /**
     * Grant permissions by slug(/s).
     */
    public function grantPermissionBySlug(array|string $slug): void
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
     */
    public function getPermissionClass(): Permission
    {
        if (! $this->permissionClass instanceof Permission) {
            /** @var class-string $model */
            $model = config('acl.permission');
            $this->permissionClass = resolve($model);
        }

        return $this->permissionClass;
    }

    /**
     * Get related permissions.
     */
    public function permissions(): BelongsToMany
    {
        /** @var class-string $model */
        $model = config('acl.permission', Permission::class);

        return $this->belongsToMany($model)->withTimestamps();
    }

    /**
     * Grant permissions by resource.
     */
    public function grantPermissionByResource(array|string $resource): void
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
     */
    public function grantPermission(mixed $ids, array $attributes = [], bool $touch = true): void
    {
        $this->permissions()->attach($ids, $attributes, $touch);

        $this->load('permissions');
    }

    /**
     * Revoke permissions by the given slug(/s).
     */
    public function revokePermissionBySlug(array|string $slug): void
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
     */
    public function revokePermissionByResource(array|string $resource): void
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
     */
    public function revokePermission(mixed $ids = null, bool $touch = true): int
    {
        $detached = $this->permissions()->detach($ids, $touch);

        $this->load('permissions');

        return $detached;
    }

    /**
     * Syncs the given permission.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array  $ids
     */
    public function syncPermissions(mixed $ids, bool $detaching = true): array
    {
        $synced = $this->permissions()->sync($ids, $detaching);

        $this->load('permissions');

        return $synced;
    }

    /**
     * Revokes all permissions.
     */
    public function revokeAllPermissions(): int
    {
        $detached = $this->permissions()->detach();

        $this->load('permissions');

        return $detached;
    }

    /**
     * Get list of permissions slug.
     */
    public function getPermissions(): array
    {
        return $this->permissions->pluck('slug')->toArray();
    }
}
