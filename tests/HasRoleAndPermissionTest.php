<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Yajra\Acl\GateRegistrar;
use Yajra\Acl\Tests\Models\UserWithPermission;

class HasRoleAndPermissionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function is_can_assign_permissions_directly_to_user()
    {
        resolve(GateRegistrar::class)->register();

        /** @var UserWithPermission $user */
        $user = UserWithPermission::forceCreate([
            'name' => 'User with Permissions',
            'email' => 'user-permission@example.com',
        ]);

        Auth::login($user);

        $this->assertFalse($user->can('article.create'));
        $this->assertFalse(auth()->user()->can('article.create'));

        $user->grantPermissionBySlug('article.create');

        $this->assertTrue($user->can('article.create'));
        $this->assertTrue(auth()->user()->can('article.create'));
    }
}
