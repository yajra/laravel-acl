<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\Acl\Models\Role;

/**
 * @property \Illuminate\Database\Eloquent\Collection|Role[] $roles
 *
 * @method static Builder havingRoles($roleIds)
 * @method static Builder havingRolesBySlugs($slugs)
 */
trait InteractsWithRole
{
    /**
     * @var class-string|Role
     */
    public $roleClass;

    /**
     * Check if user has the given role.
     *
     * @param  string|array  $role
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            $roles = $this->getRoleSlugs();

            $intersection = array_intersect($roles, (array) $role);
            $intersectionCount = count($intersection);

            return $intersectionCount > 0;
        }

        return $this->roles->contains('slug', $role);
    }

    /**
     * Get all user roles.
     */
    public function getRoleSlugs(): array
    {
        return $this->roles->pluck('slug')->toArray();
    }

    /**
     * Attach a role to user using slug.
     */
    public function attachRoleBySlug(string $slug): void
    {
        $this->attachRole($this->findRoleBySlug($slug));

        $this->load('roles');
    }

    /**
     * Attach a role to user.
     */
    public function attachRole(mixed $role, array $attributes = [], bool $touch = true): void
    {
        $this->roles()->attach($role, $attributes, $touch);

        $this->load('roles');
    }

    /**
     * Model can have many roles.
     */
    public function roles(): BelongsToMany
    {
        /** @var class-string $model */
        $model = config('acl.role', Role::class);

        return $this->belongsToMany($model)->withTimestamps();
    }

    /**
     * Find a role by slug.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function findRoleBySlug(string $slug): \Illuminate\Database\Eloquent\Model|static
    {
        return $this->getRoleClass()->newQuery()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Get Role class.
     */
    public function getRoleClass(): Role
    {
        if (! $this->roleClass instanceof Role) {
            /** @var class-string $role */
            $role = config('acl.role');

            $this->roleClass = resolve($role);
        }

        return $this->roleClass;
    }

    /**
     * Query scope for user having the given roles.
     *
     * @param  mixed  $roles
     */
    public function scopeHavingRoles(Builder $query, $roles): Builder
    {
        return $query->whereExists(function ($query) use ($roles) {
            $query->selectRaw('1')
                ->from('role_user')
                ->whereRaw('role_user.user_id = users.id')
                ->whereIn('role_id', $roles);
        });
    }

    /**
     * Query scope for user having the given roles by slugs.
     *
     * @param  mixed  $slugs
     */
    public function scopeHavingRolesBySlugs(Builder $query, $slugs): Builder
    {
        return $query->whereHas('roles', function ($query) use ($slugs) {
            $query->whereIn('roles.slug', $slugs);
        });
    }

    /**
     * Revokes the given role from the user using slug.
     *
     * @param  string|array  $slug
     * @param  bool  $touch
     */
    public function revokeRoleBySlug($slug, $touch = true): int
    {
        $roles = $this->getRoleClass()
            ->newQuery()
            ->whereIn('slug', (array) $slug)
            ->get();

        $detached = $this->roles()->detach($roles, $touch);

        $this->load('roles');

        return $detached;
    }

    /**
     * Revokes the given role from the user.
     *
     * @param  mixed  $role
     * @param  bool  $touch
     */
    public function revokeRole($role, $touch = true): int
    {
        $detached = $this->roles()->detach($role, $touch);

        $this->load('roles');

        return $detached;
    }

    /**
     * Syncs the given role(s) with the user.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array  $roles
     * @param  bool  $detaching
     */
    public function syncRoles($roles, $detaching = true): array
    {
        $synced = $this->roles()->sync($roles, $detaching);

        $this->load('roles');

        return $synced;
    }

    /**
     * Revokes all roles from the user.
     */
    public function revokeAllRoles(): int
    {
        $detached = $this->roles()->detach();

        $this->load('roles');

        return $detached;
    }
}
