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
     * @param  mixed  $permission  Permission model instance, permission slug (string), or enum that can be cast to string
     * @param  array<array-key, mixed>  $attributes
     */
    public function grantPermission(mixed $permission, array $attributes = [], bool $touch = true): void
    {
        // If permission is a string or enum, find the permission by slug
        if (is_string($permission)) {
            $permission = $this->findPermissionBySlug($permission);
        } elseif (is_object($permission) && ! ($permission instanceof Model)) {
            $permissionSlug = $this->resolvePermissionSlug($permission);
            $permission = $this->findPermissionBySlug($permissionSlug);
        }

        $this->permissions()->attach($permission, $attributes, $touch);

        $this->load('permissions');
    }

    /**
     * Get related permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Yajra\Acl\Models\Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        /** @var class-string<Permission> $model */
        $model = config('acl.permission', Permission::class);

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
     *
     * @param  mixed  $permission  Permission model instance, permission slug (string), or enum that can be cast to string
     */
    public function revokePermission(mixed $permission = null, bool $touch = true): int
    {
        // If permission is a string or enum, find the permission by slug
        if (is_string($permission)) {
            $permission = $this->findPermissionBySlug($permission);
        } elseif (is_object($permission) && ! ($permission instanceof Model)) {
            $permissionSlug = $this->resolvePermissionSlug($permission);
            $permission = $this->findPermissionBySlug($permissionSlug);
        }

        $detached = $this->permissions()->detach($permission, $touch);

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

    /**
     * Find a permission by slug.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function findPermissionBySlug(string $slug): Model|static
    {
        return $this->getPermissionClass()->newQuery()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Resolve a permission slug from various input types.
     *
     * @param  mixed  $permission  Permission slug (string) or enum
     *
     * @throws \InvalidArgumentException When the permission type is not supported
     */
    private function resolvePermissionSlug(mixed $permission): string
    {
        if (is_string($permission)) {
            return $permission;
        }

        if (is_object($permission)) {
            if ($permission instanceof \BackedEnum) {
                return (string) $permission->value;
            }

            if ($permission instanceof \UnitEnum) {
                return $permission->name;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid permission type provided. Expected string, BackedEnum, or UnitEnum, got %s.',
                    $permission::class
                )
            );
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid permission type provided. Expected string or enum, got %s.',
                gettype($permission)
            )
        );
    }
}
