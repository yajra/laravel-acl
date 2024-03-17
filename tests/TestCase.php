<?php

namespace Yajra\Acl\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Monolog\Handler\TestHandler;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Yajra\Acl\GateRegistrar;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Models\Role;
use Yajra\Acl\Tests\Models\User;

abstract class TestCase extends BaseTestCase
{
    /** @var User */
    protected $admin;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runDatabaseMigrations();
        $this->seedDatabase();
        $this->registerGates();
    }

    protected function runDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        $this->artisan('migrate');

        $this->app[Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');

            RefreshDatabaseState::$migrated = false;
        });
    }

    protected function seedDatabase(): void
    {
        Permission::createResource('article', true);
        $adminRole = $this->createRole('admin');
        $permissions = Permission::query()->pluck('id')->toArray();
        $adminRole->syncPermissions($permissions);

        $registeredRole = $this->createRole('registered');

        $this->admin = tap($this->createUser('admin'), function (User $user) use ($adminRole) {
            $user->attachRole($adminRole);
        })->fresh('roles');

        $this->user = tap($this->createUser('user'), function (User $user) use ($registeredRole) {
            $user->attachRole($registeredRole);
        })->fresh('roles');
    }

    protected function createRole(string $role): Role
    {
        return Role::create([
            'name' => Str::title($role),
            'slug' => Str::slug($role),
            'system' => true,
            'description' => "$role role.",
        ]);
    }

    protected function createUser(?string $user = null): User
    {
        $user = $user ?: Str::random(10);

        return User::create([
            'name' => Str::title($user),
            'email' => $user.'@example.com',
        ]);
    }

    protected function registerGates(): void
    {
        resolve(GateRegistrar::class)->register();
    }

    protected function createPermission(string $permission, bool $system = true): Permission
    {
        return Permission::create([
            'resource' => 'Tests',
            'name' => $permission,
            'slug' => Str::slug($permission),
            'system' => $system,
        ]);
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function resolveApplicationHttpKernel($app): void
    {
        $app->singleton(\Illuminate\Contracts\Http\Kernel::class, \Yajra\Acl\Tests\Http\Kernel::class);
    }

    /**
     * Set up the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'file');
        $app['config']->set('session.expire_on_close', false);

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('view.paths', [__DIR__.'/resources/views']);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['log']->getLogger()->pushHandler(new TestHandler());
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Yajra\Acl\AclServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [];
    }
}
