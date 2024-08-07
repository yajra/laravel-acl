<?php

namespace Yajra\Acl;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Yajra\Acl\Models\Permission;

class GateRegistrar
{
    public function __construct(public GateContract $gate, public Repository $cache) {}

    public function register(): void
    {
        $this->getPermissions()->each(function (Permission $permission) {
            $ability = $permission->slug;
            $policy = function (User $user) use ($permission) {
                if (method_exists($user, 'getPermissions')) {
                    // @phpstan-ignore-next-line
                    return collect($user->getPermissions())->contains($permission->slug);
                }

                return false;
            };

            if (Str::contains($permission->slug, '@')) {
                $policy = $permission->slug;
                $ability = $permission->name;
            }

            $this->gate->define($ability, $policy);
        });
    }

    /**
     * Get all permissions.
     *
     * @return \Illuminate\Support\Collection<array-key, Permission>
     */
    protected function getPermissions(): Collection
    {
        /** @var string $key */
        $key = config('acl.cache.key', 'permissions.policies');

        try {
            return config('acl.cache.enabled', true)
                ? $this->cache->rememberForever($key, fn () => $this->getPermissionsFromQuery())
                : $this->getPermissionsFromQuery();
        } catch (\Throwable) {
            $this->cache->forget($key);

            return collect();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<array-key, Permission>
     */
    public function getPermissionsFromQuery(): Collection
    {
        return $this->getPermissionClass()
            ->with('roles')
            ->get();
    }

    protected function getPermissionClass(): Permission
    {
        /** @var class-string<Permission> $class */
        $class = config('acl.permission', Permission::class);

        return resolve($class);
    }
}
