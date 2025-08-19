<?php

namespace Yajra\Acl\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Tests\Enums\RoleEnum;
use Yajra\Acl\Tests\TestCase;

class RoleTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_can_revoke_all_roles()
    {
        $this->assertEquals(1, $this->admin->roles->count());

        $this->admin->revokeAllRoles();

        $this->assertEquals(0, $this->admin->roles->count());
    }

    #[Test]
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

    #[Test]
    public function it_can_revoke_role_using_enum()
    {
        $user = $this->createUser();

        // Create roles that match our enum values
        $adminRole = $this->createRole('test-admin');
        $managerRole = $this->createRole('test-manager');

        // Grant roles first
        $user->attachRole($adminRole);
        $user->attachRole($managerRole);
        $this->assertCount(2, $user->roles);

        // Test revoking role using enum
        $user->revokeRole(RoleEnum::TEST_ADMIN);
        $this->assertFalse($user->hasRole('test-admin'));
        $this->assertTrue($user->hasRole('test-manager'));

        // Verify we now have 1 role
        $this->assertCount(1, $user->roles);
    }

    #[Test]
    public function it_can_revoke_role_using_string()
    {
        $user = $this->createUser();

        // Create role that matches string slug
        $moderatorRole = $this->createRole('moderator');

        // Grant role first
        $user->attachRole($moderatorRole);
        $this->assertTrue($user->hasRole('moderator'));

        // Test revoking role using string
        $user->revokeRole('moderator');
        $this->assertFalse($user->hasRole('moderator'));

        $this->assertCount(0, $user->roles);
    }

    #[Test]
    public function it_throws_exception_for_invalid_role_enum_on_revoke()
    {
        $user = $this->createUser();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role type provided');

        // Pass an invalid object type
        $user->revokeRole(new \stdClass);
    }

    #[Test]
    public function it_throws_exception_for_nonexistent_role_slug_on_revoke()
    {
        $user = $this->createUser();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Try to revoke a role that doesn't exist
        $user->revokeRole('nonexistent-role');
    }

    #[Test]
    public function it_can_query_users_having_roles_with_mixed_types()
    {
        // Create test roles
        $adminRole = $this->createRole('test-admin');
        $managerRole = $this->createRole('test-manager');
        $moderatorRole = $this->createRole('moderator');
        $guestRole = $this->createRole('guest');

        // Create test users with different roles
        $user1 = $this->createUser();
        $user1->attachRole($adminRole);

        $user2 = $this->createUser();
        $user2->attachRole($managerRole);

        $user3 = $this->createUser();
        $user3->attachRole($moderatorRole);

        $user4 = $this->createUser();
        $user4->attachRole($guestRole);

        $user5 = $this->createUser(); // No roles

        // Test mixed array with role ID, string slug, enum, and model instance
        $mixedRoles = [
            $adminRole->id,              // Role ID (integer)
            'test-manager',              // String slug
            RoleEnum::MODERATOR,         // Enum
            $guestRole,                   // Model instance
        ];

        $usersWithRoles = $user1::havingRoles($mixedRoles)->get();

        // Should return users 1, 2, 3, and 4 (all except user5)
        $this->assertCount(4, $usersWithRoles);

        $userIds = $usersWithRoles->pluck('id')->toArray();
        $this->assertContains($user1->id, $userIds);
        $this->assertContains($user2->id, $userIds);
        $this->assertContains($user3->id, $userIds);
        $this->assertContains($user4->id, $userIds);
        $this->assertNotContains($user5->id, $userIds);
    }

    #[Test]
    public function it_can_query_users_having_roles_with_only_enums()
    {
        // Create test roles that match enum values
        $adminRole = $this->createRole('test-admin');
        $moderatorRole = $this->createRole('moderator');

        // Create test users
        $user1 = $this->createUser();
        $user1->attachRole($adminRole);

        $user2 = $this->createUser();
        $user2->attachRole($moderatorRole);

        $user3 = $this->createUser(); // No roles

        // Test with only enum values
        $enumRoles = [RoleEnum::TEST_ADMIN, RoleEnum::MODERATOR];

        $usersWithRoles = $user1::havingRoles($enumRoles)->get();

        // Should return users 1 and 2
        $this->assertCount(2, $usersWithRoles);

        $userIds = $usersWithRoles->pluck('id')->toArray();
        $this->assertContains($user1->id, $userIds);
        $this->assertContains($user2->id, $userIds);
        $this->assertNotContains($user3->id, $userIds);
    }

    #[Test]
    public function it_can_query_users_having_roles_with_only_strings()
    {
        // Create test roles with unique names
        $editorRole = $this->createRole('editor');
        $writerRole = $this->createRole('writer');

        // Create test users
        $user1 = $this->createUser();
        $user1->attachRole($editorRole);

        $user2 = $this->createUser();
        $user2->attachRole($writerRole);

        $user3 = $this->createUser(); // No roles

        // Test with only string slugs
        $stringRoles = ['editor', 'writer'];

        $usersWithRoles = $user1::havingRoles($stringRoles)->get();

        // Should return users 1 and 2
        $this->assertCount(2, $usersWithRoles);

        $userIds = $usersWithRoles->pluck('id')->toArray();
        $this->assertContains($user1->id, $userIds);
        $this->assertContains($user2->id, $userIds);
        $this->assertNotContains($user3->id, $userIds);
    }

    #[Test]
    public function it_returns_empty_result_when_no_users_have_specified_roles()
    {
        // Create users with different roles
        $user1 = $this->createUser();
        $user1->attachRole($this->createRole('editor'));

        $user2 = $this->createUser();
        $user2->attachRole($this->createRole('viewer'));

        // Search for roles that don't exist on any user
        $nonExistentRoles = ['super-admin', RoleEnum::TEST_ADMIN];

        $usersWithRoles = $user1::havingRoles($nonExistentRoles)->get();

        $this->assertCount(0, $usersWithRoles);
    }
}
