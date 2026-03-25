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
     * @return Collection<array-key, Permission>
     */
    protected function getPermissions(): Collection
    {
        /** @var string $key */
        $key = config('acl.cache.key', 'permissions.policies');

        try {
            if (! config('acl.cache.enabled', true)) {
                return $this->getPermissionsFromQuery();
            }

            $payload = $this->cache->rememberForever(
                $key,
                fn () => $this->permissionsToCachePayload($this->getPermissionsFromQuery())
            );

            return $this->hydratePermissionsFromCache($payload);
        } catch (\Throwable) {
            $this->cache->forget($key);

            return collect();
        }
    }

    /**
     * Plain arrays for cache storage (Laravel may forbid unserializing arbitrary PHP classes).
     *
     * @param  Collection<int, Permission>  $permissions
     * @return array<int, array<string, mixed>>
     */
    protected function permissionsToCachePayload(Collection $permissions): array
    {
        return $permissions->map(fn (Permission $permission) => $permission->getAttributes())->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return Collection<int, Permission>
     */
    protected function hydratePermissionsFromCache(array $rows): Collection
    {
        $model = $this->getPermissionClass();

        return collect($rows)->map(fn (array $attributes) => $model->newFromBuilder($attributes));
    }

    /**
     * @return Collection<array-key, Permission>
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
