<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Yajra\Acl\Models\Permission;

/**
 * @property \Illuminate\Database\Eloquent\Collection<array-key, Permission> $permissions
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait InteractsWithPermission
{
    /**
     * @var class-string<Permission>|Permission
     */
    public $permissionClass;

    /**
     * Grant permissions by slug(/s).
     *
     * @param  string|string[]  $slug
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
     * Grant the given permission.
     *
     * @param  array<array-key, mixed>  $attributes
     */
    public function grantPermission(mixed $ids, array $attributes = [], bool $touch = true): void
    {
        $this->permissions()->attach($ids, $attributes, $touch);

        $this->load('permissions');
    }

    /**
     * Get related permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Permission>
     */
    public function permissions(): BelongsToMany
    {
        /** @var class-string<Permission> $model */
        $model = config('acl.permission', Permission::class);

        // @phpstan-ignore-next-line
        return $this->belongsToMany($model)->withTimestamps();
    }

    /**
     * Grant permissions by resource.
     *
     * @param  string|string[]  $resource
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
     * Revoke permissions by the given slug(/s).
     *
     * @param  string|string[]  $slug
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
     * Revokes the given permission.
     */
    public function revokePermission(mixed $ids = null, bool $touch = true): int
    {
        $detached = $this->permissions()->detach($ids, $touch);

        $this->load('permissions');

        return $detached;
    }

    /**
     * Revoke permissions by resource.
     *
     * @param  string|string[]  $resource
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
     * Syncs the given permission.
     *
     * @param  Collection<array-key, Permission>|Model|array<array-key, int>  $ids
     * @return array<array-key, mixed>
     */
    public function syncPermissions(Collection|Model|array $ids, bool $detaching = true): array
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
     *
     * @return array<array-key, mixed>
     */
    public function getPermissions(): array
    {
        $this->loadMissing('permissions');

        return $this->permissions->pluck('slug')->toArray();
    }
}
