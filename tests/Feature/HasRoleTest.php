<?php

namespace Yajra\Acl\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Yajra\Acl\Models\Role;
use Yajra\Acl\Tests\Enums\RoleEnum;
use Yajra\Acl\Tests\Models\User;
use Yajra\Acl\Tests\TestCase;

class HasRoleTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_attach_role_to_user()
    {
        $role = $this->createRole('Test');
        $user = $this->createUser('Yajra');

        $this->assertCount(0, $user->roles);

        $user->attachRole($role);
        $this->assertCount(1, $user->roles);
    }

    #[Test]
    public function it_can_attach_role_to_user_by_slug()
    {
        $this->createRole('Test');
        $user = $this->createUser('Yajra');

        $this->assertCount(0, $user->roles);

        $user->attachRoleBySlug('test');
        $this->assertCount(1, $user->roles);
    }

    #[Test]
    public function it_can_revoke_user_role()
    {
        $role = $this->createRole('Test');
        $user = $this->createUser('Yajra');

        $user->attachRole($role);
        $this->assertCount(1, $user->roles);

        $user->revokeRole($role);
        $this->assertCount(0, $user->roles);
    }

    #[Test]
    public function it_can_revoke_user_role_by_slug()
    {
        $this->createRole('Test');
        $user = $this->createUser('Yajra');

        $user->attachRoleBySlug('test');
        $this->assertCount(1, $user->roles);

        $user->revokeRoleBySlug('test');
        $this->assertCount(0, $user->roles);
    }

    #[Test]
    public function it_can_revoke_all_user_roles()
    {
        $role1 = $this->createRole('one');
        $role2 = $this->createRole('two');
        $user = $this->createUser('Yajra');

        $user->attachRole($role1);
        $user->attachRole($role2);
        $this->assertCount(2, $user->roles);

        $user->revokeAllRoles();
        $this->assertCount(0, $user->roles);
    }

    #[Test]
    public function it_can_revoke_all_roles()
    {
        $this->assertEquals(1, $this->admin->roles->count());

        $this->admin->revokeAllRoles();

        $this->assertEquals(0, $this->admin->roles->count());
    }

    #[Test]
    public function it_can_sync_user_roles()
    {
        $roles = Role::query()->whereIn('slug', ['admin', 'registered'])->get();

        $this->assertCount(1, $this->admin->roles);
        $this->admin->syncRoles($roles);
        $this->assertCount(2, $this->admin->roles);
    }

    #[Test]
    public function it_can_authorize_user_access()
    {
        Auth::login($this->admin);

        /** @var Role $role */
        $role = $this->admin->roles->first();

        $this->assertTrue(Gate::allows('create-article'));
        $this->assertTrue(Auth::user()->can('create-article'));
        $this->assertTrue($this->admin->can('create-article'));

        $role->revokePermissionBySlug('create-article');

        $this->assertTrue(Gate::denies('create-article'));
        $this->assertTrue(Auth::user()->cannot('create-article'));
        $this->assertTrue($this->admin->cannot('create-article'));
    }

    #[Test]
    public function it_can_authorize_user_with_at_least_one_matching_permission()
    {
        Auth::login($this->admin);

        /** @var Role $role */
        $role = $this->admin->roles->first();

        $permissions = ['create-article', 'update-article'];
        $this->assertTrue(Gate::any($permissions));
        $this->assertTrue(Auth::user()->canAtLeast($permissions));
        $this->assertTrue($this->admin->canAtLeast($permissions));

        $role->revokePermissionBySlug('create-article');

        $this->assertTrue(Gate::any($permissions));
        $this->assertTrue(Auth::user()->canAtLeast($permissions));
        $this->assertTrue($this->admin->canAtLeast($permissions));
    }

    #[Test]
    public function it_can_authorize_user_with_at_least_one_matching_permission_or_role()
    {
        Auth::login($this->admin);

        /** @var Role $role */
        $role = $this->admin->roles->first();

        $permissions = ['create-article', 'admin'];
        $this->assertTrue(Auth::user()->canAccess($permissions));
        $this->assertTrue($this->admin->canAccess($permissions));

        $role->revokePermissionBySlug('create-article');

        $this->assertTrue(Auth::user()->canAccess($permissions));
        $this->assertTrue($this->admin->canAccess($permissions));
    }

    #[Test]
    public function it_can_check_user_with_role()
    {
        Auth::login($this->admin);

        $this->assertTrue(Auth::user()->hasRole('admin'));
        $this->assertTrue($this->admin->hasRole('admin'));

        $this->admin->revokeRoleBySlug('admin');

        $this->assertFalse(Auth::user()->hasRole('admin'));
        $this->assertFalse($this->admin->hasRole('admin'));
    }

    #[Test]
    public function it_can_query_with_having_roles_scopes()
    {
        $adminUser = $this->createUser('Yajra');
        $supportUser = $this->createUser('Kloe');
        $adminUser->attachRole($manager = $this->createRole('manager'));
        $adminUser->attachRole($user = $this->createRole('user'));
        $adminUser->attachRole($support = $this->createRole('support'));

        $supportUser->attachRole($support);

        $roles = (new User)->havingRoles([$manager->getKey(), $support->getKey()])->get();
        $this->assertCount(2, $roles);

        $roles = (new User)->havingRolesBySlugs([$manager->slug, $user->slug])->get();
        $this->assertCount(1, $roles);
    }

    #[Test]
    public function it_can_attach_role_to_user_using_enum()
    {
        // Create roles that match our enum values
        $this->createRole('test-admin');  // This will create slug 'test-admin'
        $this->createRole('test-manager'); // This will create slug 'test-manager'
        $user = $this->createUser('Yajra');

        $this->assertCount(0, $user->roles);

        // Test attaching role using enum
        $user->attachRole(RoleEnum::TEST_ADMIN);
        $this->assertCount(1, $user->roles);
        $this->assertTrue($user->hasRole('test-admin'));

        // Test attaching another role using enum
        $user->attachRole(RoleEnum::TEST_MANAGER);
        $this->assertCount(2, $user->roles);
        $this->assertTrue($user->hasRole('test-manager'));

        // Verify both roles are attached
        $rolesSlugs = $user->getRoleSlugs();
        $this->assertContains('test-admin', $rolesSlugs);
        $this->assertContains('test-manager', $rolesSlugs);
    }

    #[Test]
    public function it_can_attach_role_using_string_slug()
    {
        $this->createRole('user'); // This will create slug 'user'
        $user = $this->createUser('Yajra');

        $this->assertCount(0, $user->roles);

        // Test attaching role using string slug directly with attachRole
        $user->attachRole('user');
        $this->assertCount(1, $user->roles);
        $this->assertTrue($user->hasRole('user'));
    }

    #[Test]
    public function it_can_check_user_role_using_enum()
    {
        // Create roles that match our enum values
        $this->createRole('test-admin');
        $this->createRole('test-manager');
        $user = $this->createUser('Yajra');

        // Attach role using enum
        $user->attachRole(RoleEnum::TEST_ADMIN);

        // Test hasRole with enum
        $this->assertTrue($user->hasRole(RoleEnum::TEST_ADMIN));
        $this->assertFalse($user->hasRole(RoleEnum::TEST_MANAGER));

        // Test hasRole still works with string
        $this->assertTrue($user->hasRole('test-admin'));
        $this->assertFalse($user->hasRole('test-manager'));

        // Attach another role
        $user->attachRole(RoleEnum::TEST_MANAGER);

        // Test both roles
        $this->assertTrue($user->hasRole(RoleEnum::TEST_ADMIN));
        $this->assertTrue($user->hasRole(RoleEnum::TEST_MANAGER));
    }

    #[Test]
    public function it_can_check_user_role_with_array_including_enums()
    {
        $this->createRole('test-admin');
        $this->createRole('moderator');
        $user = $this->createUser('Yajra');

        $user->attachRole(RoleEnum::TEST_ADMIN);

        // Test hasRole with array of strings
        $this->assertTrue($user->hasRole(['test-admin', 'moderator']));
        $this->assertTrue($user->hasRole(['test-admin']));
        $this->assertFalse($user->hasRole(['moderator', 'guest']));
    }

    #[Test]
    public function it_throws_exception_for_invalid_role_type_in_has_role()
    {
        $user = $this->createUser('Yajra');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role type provided. Expected string, BackedEnum, or UnitEnum, got stdClass.');

        $user->hasRole(new \stdClass);
    }

    #[Test]
    public function it_throws_exception_for_invalid_role_type_in_attach_role()
    {
        $user = $this->createUser('Yajra');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role type provided. Expected string, BackedEnum, or UnitEnum, got stdClass.');

        $user->attachRole(new \stdClass);
    }

    #[Test]
    public function it_throws_exception_for_invalid_primitive_type_in_resolve_role_slug()
    {
        $user = $this->createUser('Yajra');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role type provided. Expected string or enum, got integer.');

        $user->hasRole(123);
    }
}
