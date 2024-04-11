<?php

namespace Yajra\Acl;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;
use Yajra\Acl\Models\Permission;

class GateRegistrar
{
    public function __construct(public GateContract $gate, public Repository $cache)
    {
    }

    public function register(): void
    {
        collect($this->getPermissions())->each(function ($data) {
            $permission = new Permission($data);

            $ability = $permission->slug;
            $policy = function (User $user) use ($permission) {
                if (method_exists($user, 'getPermissions')) {
                    // @phpstan-ignore-next-line
                    $permissions = collect($user->getPermissions());

                    return $permissions->contains($permission->slug);
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
     * @return array<array<string, mixed>>
     */
    protected function getPermissions(): array
    {
        /** @var string $key */
        $key = config('acl.cache.key', 'permissions.policies');

        try {
            if (config('acl.cache.enabled', true)) {
                return $this->cache->rememberForever($key, fn () => $this->getPermissionsFromQuery());
            } else {
                return $this->getPermissionsFromQuery();
            }
        } catch (\Throwable) {
            $this->cache->forget($key);

            return [];
        }
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getPermissionsFromQuery(): array
    {
        // @phpstan-ignore-next-line
        return $this->getPermissionClass()
            ->with('roles')
            ->get()
            ->toArray();
    }

    protected function getPermissionClass(): Permission
    {
        /** @var class-string<Permission> $class */
        $class = config('acl.permission', Permission::class);

        return resolve($class);
    }
}
