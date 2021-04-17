<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\Acl\Models\Permission;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property Permission[]|\Illuminate\Database\Eloquent\Collection $permissions
 */
trait HasRoleAndPermission
{
    use HasRole {
        HasRole::getPermissions as getRolePermissions;
    }

    /**
     * Grants the given permission to the user.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array  $id
     * @param  array  $attributes
     * @param  bool  $touch
     * @return void
     */
    public function grantPermission($id, array $attributes = [], $touch = true)
    {
        $this->permissions()->attach($id, $attributes, $touch);
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
     * Revokes the given permission from the user.
     *
     * @param  mixed  $ids
     * @param  bool  $touch
     * @return int
     */
    public function revokePermission($ids = null, $touch = true): int
    {
        return $this->permissions()->detach($ids, $touch);
    }

    /**
     * Syncs the given permission(s) with the user.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array  $ids
     * @param  bool  $detaching
     * @return array
     */
    public function syncPermissions($ids, $detaching = true): array
    {
        return $this->permissions()->sync($ids, $detaching);
    }

    /**
     * Revokes all permissions from the user.
     *
     * @return int
     */
    public function revokeAllPermissions(): int
    {
        return $this->permissions()->detach();
    }

    /**
     * Get all user permissions slug.
     *
     * @return array|null
     */
    public function getPermissions(): array
    {
        $rolePermissions = $this->getRolePermissions();
        $userPermissions = $this->permissions->pluck('slug')->toArray();

        return collect($userPermissions)->merge($rolePermissions)->unique()->toArray();
    }
}
