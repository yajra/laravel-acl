<?php

namespace Yajra\Acl\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Str;
use Monolog\Handler\TestHandler;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Models\Role;
use Yajra\Acl\Tests\Models\User;

abstract class TestCase extends BaseTestCase
{
    protected $admin;

    protected $user;

    protected function setUp()
    {
        parent::setUp();

        $this->setupAuthRoutes();
        $this->runDatabaseMigrations();
        $this->seedDatabase();
    }

    protected function setupAuthRoutes()
    {
        $this->app['router']->auth();
    }

    protected function runDatabaseMigrations()
    {
        /** @var \Illuminate\Database\Schema\Builder $schemaBuilder */
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
        $schemaBuilder->create('users', function (Blueprint $table) {
            $table->increments('id');
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

    protected function seedDatabase()
    {
        Permission::createResource('article', true);
        $adminRole   = $this->createRole('admin');
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

    /**
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|Role
     */
    protected function createRole($role)
    {
        return Role::query()->create([
            'name'        => Str::title($role),
            'slug'        => str_slug($role),
            'system'      => true,
            'description' => "{$role} role.",
        ]);
    }

    /**
     * @param string $user
     * @return User|\Illuminate\Database\Eloquent\Model
     */
    protected function createUser($user = null)
    {
        $user = $user ?: str_random(10);

        return User::query()->forceCreate([
            'name'  => Str::title($user),
            'email' => $user . '@example.com',
        ]);
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'Yajra\Acl\Tests\Http\Kernel');
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'file');
        $app['config']->set('session.expire_on_close', false);

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('view.paths', [__DIR__ . '/resources/views']);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['log']->getMonolog()->pushHandler(new TestHandler());
    }

    protected function getPackageProviders($app)
    {
        return [
            \Yajra\Acl\AclServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [];
    }
}
