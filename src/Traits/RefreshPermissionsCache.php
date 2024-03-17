<?php

namespace Yajra\Acl\Traits;

use Illuminate\Database\Eloquent\Model;
use Yajra\Acl\GateRegistrar;

trait RefreshPermissionsCache
{
    public static function bootRefreshPermissionsCache(): void
    {
        static::saved(function () {
            if (auth()->check() && auth()->user() instanceof Model) {
                auth()->user()->load('roles');
            }

            /** @var string $key */
            $key = config('acl.cache.key', 'permissions.policies');

            app('cache.store')->forget($key);
            app(GateRegistrar::class)->register();
        });

        static::deleted(function () {
            if (auth()->check() && auth()->user() instanceof Model) {
                auth()->user()->load('roles');
            }

            /** @var string $key */
            $key = config('acl.cache.key', 'permissions.policies');

            app('cache.store')->forget($key);
            app(GateRegistrar::class)->register();
        });
    }
}
