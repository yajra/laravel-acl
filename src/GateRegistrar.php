<?php

namespace Yajra\Acl;

use Exception;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Yajra\Acl\Models\Permission;

class GateRegistrar
{
    /**
     * GateRegistrar constructor.
     */
    public function __construct(public GateContract $gate, public Repository $cache)
    {
    }

    /**
     * Handle permission gate registration.
     */
    public function register(): void
    {
        // @phpstan-ignore-next-line
        $this->getPermissions()->each(function (Permission $permission) {
            $ability = $permission->slug;
            $policy = fn ($user) => // @phpstan-ignore-next-line
collect($user->getPermissions())->contains($permission->slug);

            if (Str::contains($permission->slug, '@')) {
                $policy = $permission->slug;
                $ability = $permission->name;
            }

            $this->gate->define($ability, $policy);
        });
    }

    /**
     * Get all permissions.
     */
    protected function getPermissions(): Collection
    {
        /** @var string $key */
        $key = config('acl.cache.key', 'permissions.policies');

        try {
            if (config('acl.cache.enabled', true)) {
                // @phpstan-ignore-next-line
                return $this->cache->rememberForever($key, fn () => $this->getPermissionClass()->with('roles')->get());
            } else {
                return $this->getPermissionClass()->with('roles')->get();
            }
        } catch (Exception) {
            $this->cache->forget($key);

            return new Collection;
        }
    }

    protected function getPermissionClass(): Permission
    {
        /** @var class-string $class */
        $class = config('acl.permission', Permission::class);

        return resolve($class);
    }
}
