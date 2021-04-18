<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Yajra\Acl\Models\Role;

/**
 * @property \Illuminate\Database\Eloquent\Collection roles
 * @method static Builder havingRoles(array $roleIds)
 * @method static Builder havingRolesBySlugs(array $slugs)
 */
trait HasRole
{
    private $roleClass;

    /**
     * Check if user have access using any of the acl (permissions or roles slug).
     *
     * @param  string|array  $acl
     * @return boolean
     */
    public function canAccess($acl): bool
    {
        return $this->canAtLeast($acl) || $this->hasRole($acl);
    }

    /**
     * Check if user has at least one of the given permissions
     *
     * @param  string|array  $permissions
     * @return bool
     */
    public function canAtLeast($permissions): bool
    {
        $can = false;

        if (auth()->check()) {
            /** @var Role $role */
            foreach ($this->roles as $role) {
                if ($role->canAtLeast($permissions)) {
                    $can = true;
                }
            }
        } else {
            try {
                $guest = $this->findRoleBySlug('guest');

                return $guest->canAtLeast($permissions);
            } catch (ModelNotFoundException $exception) {
                //
            }
        }

        return $can;
    }

    /**
     * Find a role by slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Database\Eloquent\Model|static
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function findRoleBySlug(string $slug): Role
    {
        return $this->getRoleClass()->newQuery()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Get Role class.
     *
     * @return Role
     */
    public function getRoleClass(): Role
    {
        if (!isset($this->roleClass)) {
            $this->roleClass = resolve(config('acl.role'));
        }

        return $this->roleClass;
    }

    /**
     * Check if user has the given role.
     *
     * @param  string|array  $role
     * @return bool
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
     *
     * @return array
     */
    public function getRoleSlugs(): array
    {
        return $this->roles->pluck('slug')->toArray();
    }

    /**
     * Attach a role to user using slug.
     *
     * @param  string  $slug
     */
    public function attachRoleBySlug(string $slug)
    {
        $this->attachRole($this->findRoleBySlug($slug));
    }

    /**
     * Attach a role to user.
     *
     * @param  mixed  $role
     */
    public function attachRole($role)
    {
        $this->roles()->attach($role);
    }

    /**
     * Model can have many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('acl.role', Role::class))->withTimestamps();
    }

    /**
     * Query scope for user having the given roles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $roles
     * @return \Illuminate\Database\Eloquent\Builder
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
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $slugs
     * @return \Illuminate\Database\Eloquent\Builder
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
     * @param  string|array  $slug
     * @param  bool  $touch
     * @return int
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
     * @return int
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
     * @return array
     */
    public function syncRoles($roles, $detaching = true): array
    {
        $synced = $this->roles()->sync($roles, $detaching);

        $this->load('roles');

        return $synced;
    }

    /**
     * Revokes all roles from the user.
     *
     * @return int
     */
    public function revokeAllRoles(): int
    {
        $detached = $this->roles()->detach();

        $this->load('roles');

        return $detached;
    }

    /**
     * Get all user role permissions.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        $permissions = [[], []];

        foreach ($this->roles as $role) {
            $permissions[] = $role->getPermissions();
        }

        return call_user_func_array('array_merge', $permissions);
    }

    /**
     * Check if the given entity/model is owned by the user.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entity
     * @param  string  $relation
     * @return bool
     */
    public function owns(Model $entity, $relation = 'user_id'): bool
    {
        return $this->getKeyName() === $entity->{$relation};
    }
}
