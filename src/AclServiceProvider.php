<?php

namespace Yajra\Acl;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class AclServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     *
     * @param  GateRegistrar  $gate
     * @return void
     */
    public function boot(GateRegistrar $gate)
    {
        $gate->register();

        $this->publishConfig();
        $this->publishMigrations();
        $this->registerPolicies();
        $this->registerBladeDirectives();
    }

    /**
     * Publish package config file.
     */
    protected function publishConfig()
    {
        $path = __DIR__.'/../config/acl.php';

        $this->publishes([$path => config_path('acl.php')], 'laravel-acl');

        $this->mergeConfigFrom($path, 'acl');
    }

    /**
     * Publish package migration files.
     */
    protected function publishMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->publishes([
            __DIR__.'/../migrations' => database_path('migrations'),
        ], 'laravel-acl');
    }

    /**
     * Register custom blade directives.
     */
    protected function registerBladeDirectives()
    {
        /** @var BladeCompiler $blade */
        $blade = $this->app['blade.compiler'];
        $blade->directive('canAtLeast', function ($expression) {
            return "<?php if (app('laravel-acl.directives.canAtLeast')->handle({$expression})): ?>";
        });
        $blade->directive('endCanAtLeast', function ($expression) {
            return '<?php endif; ?>';
        });

        /** @deprecated Please use @role directive. */
        $blade->directive('isRole', function ($expression) {
            return "<?php if (app('laravel-acl.directives.role')->handle({$expression})): ?>";
        });
        $blade->directive('endIsRole', function ($expression) {
            return '<?php endif; ?>';
        });

        $blade->directive('role', function ($expression) {
            return "<?php if (app('laravel-acl.directives.role')->handle({$expression})): ?>";
        });
        $blade->directive('endRole', function ($expression) {
            return '<?php endif; ?>';
        });

        $blade->directive('hasRole', function ($expression) {
            return "<?php if (app('laravel-acl.directives.hasRole')->handle({$expression})): ?>";
        });
        $blade->directive('endHasRole', function ($expression) {
            return '<?php endif; ?>';
        });
    }

    /**
     * Register providers.
     */
    public function register()
    {
        $this->app->singleton('laravel-acl.directives.canAtLeast', Directives\CanAtLeastDirective::class);
        $this->app->singleton('laravel-acl.directives.role', Directives\RoleDirective::class);
        $this->app->singleton('laravel-acl.directives.hasRole', Directives\HasRoleDirective::class);
    }
}
