<?php

namespace Yajra\Acl\Traits;

use Yajra\Acl\GateRegistrar;

trait RefreshCache
{
    public static function bootRefreshCache()
    {
        static::saved(function () {
            if (auth()->check()) {
                auth()->user()->load('roles');
            }

            app('cache.store')->forget(config('acl.cache.key', 'permissions.policies'));
            app(GateRegistrar::class)->register();
        });

        static::deleted(function () {
            if (auth()->check()) {
                auth()->user()->load('roles');
            }

            app('cache.store')->forget(config('acl.cache.key', 'permissions.policies'));
            app(GateRegistrar::class)->register();
        });
    }
}
