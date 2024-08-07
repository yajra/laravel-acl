<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Yajra\Acl\Models\Role;

/**
 * @property \Illuminate\Database\Eloquent\Collection<array-key, Role> $roles
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
     * @param  string|string[]  $role
     */
    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            $roles = $this->getRoleSlugs();

            $intersection = array_intersect($roles, $role);
            $intersectionCount = count($intersection);

            return $intersectionCount > 0;
        }

        return $this->roles->contains('slug', $role);
    }

    /**
     * Get all user roles.
     *
     * @return array<array-key, mixed>
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
     *
     * @param  array<array-key, mixed>  $attributes
     */
    public function attachRole(mixed $role, array $attributes = [], bool $touch = true): void
    {
        $this->roles()->attach($role, $attributes, $touch);

        $this->load('roles');
    }

    /**
     * Model can have many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        /** @var class-string<Role> $model */
        $model = config('acl.role', Role::class);

        // @phpstan-ignore-next-line
        return $this->belongsToMany($model)->withTimestamps();
    }

    /**
     * Find a role by slug.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function findRoleBySlug(string $slug): Model|static
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
     * @param  \Illuminate\Database\Eloquent\Builder<\Yajra\Acl\Models\Permission>  $query
     * @param  array<array-key, int>  $roles
     * @return \Illuminate\Database\Eloquent\Builder<\Yajra\Acl\Models\Permission>
     */
    public function scopeHavingRoles(Builder $query, array $roles): Builder
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
     * @param  \Illuminate\Database\Eloquent\Builder<\Yajra\Acl\Models\Permission>  $query
     * @param  array<array-key, string>  $slugs
     * @return \Illuminate\Database\Eloquent\Builder<\Yajra\Acl\Models\Permission>
     */
    public function scopeHavingRolesBySlugs(Builder $query, array $slugs): Builder
    {
        return $query->whereHas('roles', function ($query) use ($slugs) {
            $query->whereIn('roles.slug', $slugs);
        });
    }

    /**
     * Revokes the given role from the user using slug.
     *
     * @param  string|string[]  $slug
     * @param  bool  $touch
     */
    public function revokeRoleBySlug(string|array $slug, $touch = true): int
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
     * @param  bool  $touch
     */
    public function revokeRole(mixed $role, $touch = true): int
    {
        $detached = $this->roles()->detach($role, $touch);

        $this->load('roles');

        return $detached;
    }

    /**
     * Syncs the given role(s) with the user.
     *
     * @param  Collection<array-key, mixed>|Model|array<array-key, int>  $roles
     * @return array<array-key, mixed>
     */
    public function syncRoles(Collection|Model|array $roles, bool $detaching = true): array
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
