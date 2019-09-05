<?php

namespace Yajra\Acl\Traits;

trait RefreshCache
{
    public static function bootRefreshCache()
    {
        static::saved(function () {
            if (auth()->check()) {
                auth()->user()->load('roles');
            }

            app('cache.store')->forget(config('acl.cache.key', 'permissions.policies'));
        });

        static::deleted(function () {
            if (auth()->check()) {
                auth()->user()->load('roles');
            }

            app('cache.store')->forget(config('acl.cache.key', 'permissions.policies'));
        });
    }
}
