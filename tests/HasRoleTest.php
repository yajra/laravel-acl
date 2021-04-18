<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Models\Role;

class HasRoleTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function is_can_grant_permission()
    {
        $role = $this->createRole('Test');
        $permission = $this->createPermission('Test');

        $this->assertEquals(0, $role->permissions->count());
        $this->assertFalse($role->can($permission->slug));

        $role->grantPermission($permission);

        $this->assertEquals(1, $role->permissions->count());
        $this->assertTrue($role->can($permission->slug));
    }

    /** @test */
    public function is_can_grant_permission_by_resource()
    {
        $role = $this->createRole('Resource');
        $permissions = Permission::createResource('Tests');

        $this->assertEquals(0, $role->permissions->count());
        $this->assertFalse($role->can($permissions->first->slug));

        $role->grantPermissionByResource('Tests');

        $this->assertEquals(5, $role->permissions->count());
        $permissions->each(function ($permission) use ($role) {
            $this->assertTrue($role->can($permission->slug));
        });
    }

    /** @test */
    public function is_can_grant_permission_by_slug()
    {
        $role = $this->createRole('Test');
        $permission = $this->createPermission('Test');

        $this->assertEquals(0, $role->permissions->count());
        $this->assertFalse($role->can($permission->slug));

        $role->grantPermissionBySlug($permission->slug);

        $this->assertEquals(1, $role->permissions->count());
        $this->assertTrue($role->can($permission->slug));
    }

    /** @test */
    public function it_can_revoke_permission_by_slug()
    {
        Auth::login($this->admin);

        /** @var Role $role */
        $role = Role::findBySlug('admin');

        $this->assertTrue($role->can('article.create'));
        $this->assertTrue(auth()->user()->fresh()->can('article.create'));

        $role->revokePermissionBySlug('article.create');

        $this->assertFalse($role->can('article.create'));
        $this->assertFalse(auth()->user()->can('article.create'));
    }

    /** @test */
    public function is_can_revoke_permission()
    {
        $role = $this->createRole('Test');
        $permission = $this->createPermission('Test');
        $role->grantPermission($permission);

        $this->assertEquals(1, $role->permissions->count());
        $this->assertTrue($role->can($permission->slug));

        $role->revokePermission($permission);

        $this->assertEquals(0, $role->permissions->count());
        $this->assertFalse($role->can($permission->slug));
    }

    /** @test */
    public function is_can_revoke_permission_by_slug()
    {
        $role = $this->createRole('Test');
        $permission = $this->createPermission('Test');
        $role->grantPermission($permission);

        $this->assertEquals(1, $role->permissions->count());
        $this->assertTrue($role->can($permission->slug));

        $role->revokePermissionBySlug($permission->slug);

        $this->assertEquals(0, $role->permissions->count());
        $this->assertFalse($role->can($permission->slug));
    }

    /** @test */
    public function is_can_revoke_permission_by_resource()
    {
        $role = $this->createRole('Resource');
        $permissions = Permission::createResource('Tests');
        $role->grantPermissionByResource('Tests');

        $this->assertEquals(5, $role->permissions->count());
        $permissions->each(function ($permission) use ($role) {
            $this->assertTrue($role->can($permission->slug));
        });

        $role->revokePermissionByResource('Tests');

        $this->assertEquals(0, $role->permissions->count());
        $this->assertFalse($role->can($permissions->first->slug));
    }

    /** @test */
    public function it_can_authorize_user_access()
    {
        Auth::login($this->admin);

        /** @var Role $role */
        $role = $this->admin->roles->first();

        $this->assertTrue(Gate::allows('article.create'));
        $this->assertTrue(Auth::user()->can('article.create'));
        $this->assertTrue($this->admin->can('article.create'));

        $role->revokePermissionBySlug('article.create');

        $this->assertTrue(Gate::denies('article.create'));
        $this->assertTrue(Auth::user()->cannot('article.create'));
        $this->assertTrue($this->admin->cannot('article.create'));
    }
}
