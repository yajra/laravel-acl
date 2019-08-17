<?php

namespace Yajra\Acl;

use Illuminate\Support\Str;
use Yajra\Acl\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

class GateRegistrar
{
    /**
     * @var GateContract
     */
    private $gate;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * GateRegistrar constructor.
     *
     * @param GateContract $gate
     * @param Repository $cache
     */
    public function __construct(GateContract $gate, Repository $cache)
    {
        $this->gate  = $gate;
        $this->cache = $cache;
    }

    /**
     * Handle permission gate registration.
     */
    public function register()
    {
        $this->getPermissions()->each(function ($permission) {
            $ability = $permission->slug;
            $policy  = function ($user) use ($permission) {
                return $user->hasRole($permission->roles);
            };

            if (Str::contains($permission->slug, '@')) {
                $policy  = $permission->slug;
                $ability = $permission->name;
            }

            $this->gate->define($ability, $policy);
        });
    }

    /**
     * Get all permissions.
     *
     * @return Collection
     */
    protected function getPermissions()
    {
        $key = config('acl.cache.key', 'permissions.policies');
        try {
            if (config('acl.cache.enabled', true)) {
                return $this->cache->rememberForever($key, function () {
                    return $this->getPermissionClass()->with('roles')->get();
                });
            } else {
                return $this->getPermissionClass()->with('roles')->get();
            }
        } catch (\Exception $exception) {
            $this->cache->forget($key);

            return new Collection;
        }
    }

    /**
     * @return Permission
     */
    protected function getPermissionClass()
    {
        return resolve(config('acl.permission', Permission::class));
    }
}
