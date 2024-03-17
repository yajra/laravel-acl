<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Tests\Models\UserWithPermission;

class HasRoleAndPermissionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function is_can_authorize_user_with_permission()
    {
        $user = $this->createUserWithHasRoleAndPermission();

        Auth::login($user);

        $this->assertFalse($user->can('create-article'));
        $this->assertFalse(auth()->user()->can('create-article'));

        $user->grantPermissionBySlug('create-article');

        $this->assertTrue($user->can('create-article'));
        $this->assertTrue(auth()->user()->can('create-article'));
    }

    /**
     * @param  string|null  $user
     */
    protected function createUserWithHasRoleAndPermission($user = null): UserWithPermission
    {
        $user = $user ?: Str::random(10);

        return UserWithPermission::create([
            'name' => Str::title($user),
            'email' => $user.'@example.com',
        ]);
    }

    /** @test */
    public function it_can_grant_permission_to_user()
    {
        $user = $this->createUserWithHasRoleAndPermission();

        $this->assertCount(0, $user->permissions);

        $user->grantPermission($this->createPermission('Test'));
        $this->assertCount(1, $user->permissions);

        $user->grantPermissionBySlug('create-article');
        $this->assertCount(2, $user->permissions);

        Permission::createResource('Posts');
        $user->grantPermissionByResource('Posts');
        $this->assertCount(7, $user->permissions);
    }

    /** @test */
    public function it_can_revoke_user_permission()
    {
        $user = $this->createUserWithHasRoleAndPermission();
        Permission::createResource('Posts');
        $user->grantPermissionByResource('Posts');
        $this->assertCount(5, $user->permissions);

        $user->revokePermission(Permission::findBySlug('create-posts'));
        $this->assertCount(4, $user->permissions);

        $user->revokePermissionBySlug('view-posts');
        $this->assertCount(3, $user->permissions);

        $user->revokePermissionByResource('Posts');
        $this->assertCount(0, $user->permissions);

        Permission::createResource('Users');
        $user->grantPermissionByResource('Users');
        $this->assertCount(5, $user->permissions);
        $user->revokeAllPermissions();
        $this->assertCount(0, $user->permissions);
    }

    /** @test */
    public function it_can_return_combined_permissions_from_role_permission_and_user_permission()
    {
        $user = $this->createUserWithHasRoleAndPermission();
        $managerRole = $this->createRole('Manager');
        $supportRole = $this->createRole('Support');
        $user->attachRole($managerRole);

        Permission::createResource('Users');
        Permission::createResource('Posts');

        $managerRole->grantPermissionByResource('Users');
        $this->assertCount(5, $user->getPermissions());

        $user->grantPermissionByResource('Posts');
        $this->assertCount(10, $user->getPermissions());

        $user->attachRole($supportRole);
        $supportRole->grantPermissionByResource('Users');
        $this->assertCount(10, $user->getPermissions());
    }
}
