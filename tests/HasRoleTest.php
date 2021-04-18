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
    public function is_can_grant_role_permission()
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
    public function is_can_grant_role_permission_by_resource()
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
    public function is_can_grant_role_permission_by_slug()
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
    public function is_can_revoke_role_permission()
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
    public function is_can_revoke_role_permission_by_slug()
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
    public function it_can_revoke_all_role_permissions()
    {
        $role = $this->createRole('Test');
        $permissions = Permission::createResource('Tests');
        $role->grantPermissionByResource('Tests');

        $this->assertEquals(5, $role->permissions->count());
        $permissions->each(function ($permission) use ($role) {
            $this->assertTrue($role->can($permission->slug));
        });

        $role->revokeAllPermissions();

        $this->assertEquals(0, $role->permissions->count());
        $this->assertFalse($role->can($permissions->first->slug));
    }

    /** @test */
    public function it_can_revoke_all_roles()
    {
        $this->assertEquals(1, $this->admin->roles->count());

        $this->admin->revokeAllRoles();

        $this->assertEquals(0, $this->admin->roles->count());
    }

    /** @test */
    public function it_can_get_all_role_permissions()
    {
        $role = $this->createRole('Test');

        $permissions = $role->getPermissions();
        $this->assertCount(0, $permissions);

        $permission = $this->createPermission('Test');
        $role->grantPermission($permission);

        $permissions = $role->getPermissions();
        $this->assertCount(1, $permissions);
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

    /** @test */
    public function it_can_authorize_user_with_at_least_one_matching_permission()
    {
        Auth::login($this->admin);

        /** @var Role $role */
        $role = $this->admin->roles->first();

        $permissions = ['article.create', 'article.update'];
        $this->assertTrue(Gate::any($permissions));
        $this->assertTrue(Auth::user()->canAtLeast($permissions));
        $this->assertTrue($this->admin->canAtLeast($permissions));

        $role->revokePermissionBySlug('article.create');

        $this->assertTrue(Gate::any($permissions));
        $this->assertTrue(Auth::user()->canAtLeast($permissions));
        $this->assertTrue($this->admin->canAtLeast($permissions));
    }

    /** @test */
    public function it_can_authorize_user_with_at_least_one_matching_permission_or_role()
    {
        Auth::login($this->admin);

        /** @var Role $role */
        $role = $this->admin->roles->first();

        $permissions = ['article.create', 'admin'];
        $this->assertTrue(Auth::user()->canAccess($permissions));
        $this->assertTrue($this->admin->canAccess($permissions));

        $role->revokePermissionBySlug('article.create');

        $this->assertTrue(Auth::user()->canAccess($permissions));
        $this->assertTrue($this->admin->canAccess($permissions));
    }

    /** @test */
    public function it_can_check_user_with_role()
    {
        Auth::login($this->admin);

        $this->assertTrue(Auth::user()->hasRole('admin'));
        $this->assertTrue($this->admin->hasRole('admin'));

        $this->admin->revokeRoleBySlug('admin');

        $this->assertFalse(Auth::user()->hasRole('admin'));
        $this->assertFalse($this->admin->hasRole('admin'));
    }
}
