<?php

namespace Yajra\Acl\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Tests\Enums\PermissionEnum;
use Yajra\Acl\Tests\TestCase;

class PermissionTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_create_resource_permissions()
    {
        $permissions = Permission::createResource('Users');
        $this->assertCount(5, $permissions);

        $this->assertTrue(Permission::findBySlug('viewAny-users')->exists());
        $this->assertTrue(Permission::findBySlug('view-users')->exists());
        $this->assertTrue(Permission::findBySlug('create-users')->exists());
        $this->assertTrue(Permission::findBySlug('update-users')->exists());
        $this->assertTrue(Permission::findBySlug('delete-users')->exists());
    }

    #[Test]
    public function it_refreshes_permissions_policies_cache()
    {
        $permissions = cache('permissions.policies');
        $this->assertCount(5, $permissions);

        Permission::createResource('Posts');

        $permissions = cache('permissions.policies');
        $this->assertCount(10, $permissions);
    }

    #[Test]
    public function it_can_interacts_with_role()
    {
        /** @var Permission $permission */
        $permission = Permission::first();

        $this->assertTrue($permission->hasRole('admin'));

        $permission->attachRole($userRole = $this->createRole('user'));
        $this->assertTrue($permission->hasRole('user'));

        $permission->attachRoleBySlug('registered');
        $this->assertTrue($permission->hasRole('registered'));

        $permission->revokeRole($userRole);
        $this->assertFalse($permission->hasRole('user'));

        $permission->revokeRoleBySlug('registered');
        $this->assertFalse($permission->hasRole('registered'));

        $permission->revokeAllRoles();
        $this->assertCount(0, $permission->roles);

        $permission->syncRoles([1]);
        $this->assertCount(1, $permission->roles);
    }

    #[Test]
    public function it_can_grant_permission_using_enum()
    {
        $user = $this->createUser();

        // Create permissions that match our enum values
        Permission::create([
            'name' => 'Create Post',
            'slug' => 'create-post',
            'resource' => 'posts',
        ]);

        Permission::create([
            'name' => 'Edit Post',
            'slug' => 'edit-post',
            'resource' => 'posts',
        ]);

        // Test granting permission using enum
        $user->grantPermission(PermissionEnum::CREATE_POST);
        $this->assertTrue($user->permissions->contains('slug', 'create-post'));

        // Test granting another permission using enum
        $user->grantPermission(PermissionEnum::EDIT_POST);
        $this->assertTrue($user->permissions->contains('slug', 'edit-post'));

        // Verify we have 2 permissions
        $this->assertCount(2, $user->permissions);
    }

    #[Test]
    public function it_can_grant_permission_using_string()
    {
        $user = $this->createUser();

        // Create permission that matches string slug
        Permission::create([
            'name' => 'Delete Post',
            'slug' => 'delete-post',
            'resource' => 'posts',
        ]);

        // Test granting permission using string
        $user->grantPermission('delete-post');
        $this->assertTrue($user->permissions->contains('slug', 'delete-post'));

        $this->assertCount(1, $user->permissions);
    }

    #[Test]
    public function it_throws_exception_for_invalid_permission_enum()
    {
        $user = $this->createUser();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid permission type provided');

        // Pass an invalid object type
        $user->grantPermission(new \stdClass);
    }

    #[Test]
    public function it_throws_exception_for_nonexistent_permission_slug()
    {
        $user = $this->createUser();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Try to grant a permission that doesn't exist
        $user->grantPermission('nonexistent-permission');
    }

    #[Test]
    public function it_can_revoke_permission_using_enum()
    {
        $user = $this->createUser();

        // Create permissions that match our enum values
        $createPermission = Permission::create([
            'name' => 'Create Post',
            'slug' => 'create-post',
            'resource' => 'posts',
        ]);

        $editPermission = Permission::create([
            'name' => 'Edit Post',
            'slug' => 'edit-post',
            'resource' => 'posts',
        ]);

        // Grant permissions first
        $user->grantPermission($createPermission);
        $user->grantPermission($editPermission);
        $this->assertCount(2, $user->permissions);

        // Test revoking permission using enum
        $user->revokePermission(PermissionEnum::CREATE_POST);
        $this->assertFalse($user->permissions->contains('slug', 'create-post'));
        $this->assertTrue($user->permissions->contains('slug', 'edit-post'));

        // Verify we now have 1 permission
        $this->assertCount(1, $user->permissions);
    }

    #[Test]
    public function it_can_revoke_permission_using_string()
    {
        $user = $this->createUser();

        // Create permission that matches string slug
        $deletePermission = Permission::create([
            'name' => 'Delete Post',
            'slug' => 'delete-post',
            'resource' => 'posts',
        ]);

        // Grant permission first
        $user->grantPermission($deletePermission);
        $this->assertTrue($user->permissions->contains('slug', 'delete-post'));

        // Test revoking permission using string
        $user->revokePermission('delete-post');
        $this->assertFalse($user->permissions->contains('slug', 'delete-post'));

        $this->assertCount(0, $user->permissions);
    }

    #[Test]
    public function it_throws_exception_for_invalid_permission_enum_on_revoke()
    {
        $user = $this->createUser();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid permission type provided');

        // Pass an invalid object type
        $user->revokePermission(new \stdClass);
    }

    #[Test]
    public function it_throws_exception_for_nonexistent_permission_slug_on_revoke()
    {
        $user = $this->createUser();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Try to revoke a permission that doesn't exist
        $user->revokePermission('nonexistent-permission');
    }
}
