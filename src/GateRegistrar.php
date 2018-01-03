<?php

namespace Yajra\Acl;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Yajra\Acl\Models\Permission;

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
     * @param Repository   $cache
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
        $this->getPermissions()->each(function (Permission $permission) {
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
        try {
            return $this->cache->rememberForever('permissions.policies', function () {
                return Permission::with('roles')->get();
            });
        } catch (\Exception $exception) {
            $this->cache->forget('permissions.policies');

            return new Collection;
        }
    }
}
