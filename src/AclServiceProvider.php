<?php

namespace Yajra\Acl;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Yajra\Acl\Models\Permission;

class AclServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->registerPolicies($gate);
        $this->registerPermissions($gate);
        $this->registerCacheListener();
        $this->registerBladeDirectives();
    }

    /**
     * Publish package config file.
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
        ], 'laravel-acl');
    }

    /**
     * Publish package migration files.
     */
    protected function publishMigrations()
    {
        $this->publishes([
            __DIR__ . '/../migrations/' => database_path('migrations'),
        ], 'laravel-acl');
    }

    /**
     * Register defined permissions from database.
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate
     */
    protected function registerPermissions(GateContract $gate)
    {
        try {
            foreach ($this->getPermissions() as $permission) {
                $ability = $permission->slug;
                $policy  = function ($user) use ($permission) {
                    return $user->hasRole($permission->roles);
                };

                if (Str::contains($permission->slug, '@')) {
                    $policy  = $permission->slug;
                    $ability = $permission->name;
                }

                $gate->define($ability, $policy);
            }
        } catch (QueryException $e) {
            // \\_(",)_//
        }
    }

    /**
     * Get lists of permissions.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function getPermissions()
    {
        return $this->app['cache.store']->rememberForever('permissions.policies', function () {
            return Permission::with('roles')->get();
        });
    }

    /**
     * Register ACL models cache listener.
     */
    protected function registerCacheListener()
    {
        Permission::saved(function () {
            $this->app['cache.store']->forget('permissions.policies');
        });

        Permission::deleted(function () {
            $this->app['cache.store']->forget('permissions.policies');
        });
    }

    /**
     * Register custom blade directives.
     */
    protected function registerBladeDirectives()
    {
        /** @var BladeCompiler $blade */
        $blade = $this->app['blade.compiler'];
        $blade->directive('canAtLeast', function ($expression) {
            return "<?php echo app('Yajra\\Acl\\CanAtLeastDirective')->handle{$expression}; ?>";
        });
    }

    /**
     * Register providers.
     */
    public function register()
    {
        $this->app->singleton(CanAtLeastDirective::class, CanAtLeastDirective::class);
    }
}
