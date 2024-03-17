<?php

namespace Yajra\Acl;

use Exception;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;
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
        $this->getPermissions()->each(function (Permission $permission) {
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
     * @return \Illuminate\Database\Eloquent\Collection<array-key, \Yajra\Acl\Models\Permission>
     */
    protected function getPermissions(): Collection
    {
        /** @var string $key */
        $key = config('acl.cache.key', 'permissions.policies');

        try {
            if (config('acl.cache.enabled', true)) {
                return $this->cache->rememberForever($key, fn () => $this->getPermissionClass()->with('roles')->get());
            } else {
                return $this->getPermissionClass()->with('roles')->get();
            }
        } catch (Exception) {
            $this->cache->forget($key);

            return Collection::make();
        }
    }

    protected function getPermissionClass(): Permission
    {
        /** @var class-string<Permission> $class */
        $class = config('acl.permission', Permission::class);

        return resolve($class);
    }
}
