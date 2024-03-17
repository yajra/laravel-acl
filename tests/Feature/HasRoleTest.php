<?php

namespace Yajra\Acl\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Yajra\Acl\Models\Role;
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

        $roles = (new User())->havingRoles([$manager->getKey(), $support->getKey()])->get();
        $this->assertCount(2, $roles);

        $roles = (new User())->havingRolesBySlugs([$manager->slug, $user->slug])->get();
        $this->assertCount(1, $roles);
    }
}
