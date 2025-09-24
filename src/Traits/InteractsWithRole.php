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
     * Check if the user has the given role.
     *
     * @param  mixed  $role  Role slug (string), array of slugs, or enum
     */
    public function hasRole(mixed $role): bool
    {
        if (is_array($role)) {
            $roles = $this->getRoleSlugs();
            $intersection = array_intersect($roles, $role);

            return count($intersection) > 0;
        }

        $roleSlug = $this->resolveRoleSlug($role);

        return $this->roles->contains('slug', $roleSlug);
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
        $this->attachRole($slug);
    }

    /**
     * Attach a role to user.
     *
     * @param  mixed  $role  Role model instance, role slug (string), or enum that can be cast to string
     * @param  array<array-key, mixed>  $attributes
     */
    public function attachRole(mixed $role, array $attributes = [], bool $touch = true): void
    {
        // If role is a string or enum, find the role by slug
        if (is_string($role)) {
            $role = $this->findRoleBySlug($role);
        } elseif (is_object($role) && ! ($role instanceof Model)) {
            $roleSlug = $this->resolveRoleSlug($role);
            $role = $this->findRoleBySlug($roleSlug);
        }

        if ($this->roles->contains($role)) {
            return;
        }

        $this->roles()->attach($role, $attributes, $touch);

        $this->load('roles');
    }

    /**
     * Model can have many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Yajra\Acl\Models\Role, $this>
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
     * @param  array<array-key, mixed>  $roles  Array of role IDs, slugs, or enums
     * @return \Illuminate\Database\Eloquent\Builder<\Yajra\Acl\Models\Permission>
     */
    public function scopeHavingRoles(Builder $query, array $roles): Builder
    {
        // Separate role IDs from slugs/enums
        $roleIds = [];
        $roleSlugs = [];

        foreach ($roles as $role) {
            if (is_int($role)) {
                // It's a role ID
                $roleIds[] = $role;
            } elseif (is_string($role)) {
                // It's a role slug
                $roleSlugs[] = $role;
            } elseif (is_object($role) && ! ($role instanceof Model)) {
                // It's an enum, resolve to slug
                $roleSlugs[] = $this->resolveRoleSlug($role);
            } elseif ($role instanceof Model) {
                // It's a role model, get the ID with proper type checking
                $roleIds[] = $role->getKey();
            }
        }

        return $query->where(function ($query) use ($roleIds, $roleSlugs) {
            // Query by role IDs if we have any
            if (! empty($roleIds)) {
                $query->whereExists(function ($subQuery) use ($roleIds) {
                    $subQuery->selectRaw('1')
                        ->from('role_user')
                        ->whereRaw('role_user.user_id = users.id')
                        ->whereIn('role_id', $roleIds);
                });
            }

            // Query by role slugs if we have any
            if (! empty($roleSlugs)) {
                $query->orWhereHas('roles', function ($subQuery) use ($roleSlugs) {
                    $subQuery->whereIn('roles.slug', $roleSlugs);
                });
            }
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
     * @param  mixed  $role  Role model instance, role slug (string), or enum that can be cast to string
     * @param  bool  $touch
     */
    public function revokeRole(mixed $role, $touch = true): int
    {
        // If role is a string or enum, find the role by slug
        if (is_string($role)) {
            $role = $this->findRoleBySlug($role);
        } elseif (is_object($role) && ! ($role instanceof Model)) {
            $roleSlug = $this->resolveRoleSlug($role);
            $role = $this->findRoleBySlug($roleSlug);
        }

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

    /**
     * Resolve a role slug from various input types.
     *
     * @param  mixed  $role  Role slug (string) or enum
     *
     * @throws \InvalidArgumentException When the role type is not supported
     */
    private function resolveRoleSlug(mixed $role): string
    {
        if (is_string($role)) {
            return $role;
        }

        if (is_object($role)) {
            if ($role instanceof \BackedEnum) {
                return (string) $role->value;
            }

            if ($role instanceof \UnitEnum) {
                return $role->name;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid role type provided. Expected string, BackedEnum, or UnitEnum, got %s.',
                    $role::class
                )
            );
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid role type provided. Expected string or enum, got %s.',
                gettype($role)
            )
        );
    }
}
