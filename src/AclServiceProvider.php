<?php

namespace Yajra\Acl;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Yajra\Acl\Directives\CanAtLeastDirective;
use Yajra\Acl\Directives\RoleDirective;

class AclServiceProvider extends ServiceProvider
{
    public function boot(GateRegistrar $gate): void
    {
        $gate->register();

        $this->publishConfig();
        $this->publishMigrations();
        $this->registerPolicies();
        $this->registerBladeDirectives();
    }

    protected function publishConfig(): void
    {
        $path = __DIR__.'/../config/acl.php';

        $this->publishes([$path => config_path('acl.php')], 'laravel-acl');

        $this->mergeConfigFrom($path, 'acl');
    }

    protected function publishMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->publishes([
            __DIR__.'/../migrations' => database_path('migrations'),
        ], 'laravel-acl');
    }

    protected function registerBladeDirectives(): void
    {
        /** @var BladeCompiler $blade */
        $blade = resolve('blade.compiler');
        $blade->directive('canAtLeast', fn (string|array $expression) => "<?php if (app(CanAtLeastDirective::class)->handle({$expression})): ?>");
        $blade->directive('endCanAtLeast', fn (string|array $expression) => '<?php endif; ?>');

        $blade->directive('role', fn ($expression) => "<?php if (app(RoleDirective::class)->handle({$expression})): ?>");
        $blade->directive('endRole', fn ($expression) => '<?php endif; ?>');
    }

    public function register(): void
    {
        $this->app->singleton('laravel-acl.directives.canAtLeast', CanAtLeastDirective::class);
        $this->app->singleton('laravel-acl.directives.role', RoleDirective::class);
    }
}
