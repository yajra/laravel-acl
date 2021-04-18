<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Models\Role;

class RoleTest extends TestCase
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
}
