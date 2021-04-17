<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Yajra\Acl\GateRegistrar;
use Yajra\Acl\Models\Role;

class HasRoleTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function is_can_grant_permission_by_slug()
    {
        Auth::login($this->user);

        /** @var Role $role */
        $role = Role::findBySlug('registered');

        $this->assertFalse($role->can('article.create'));
        $this->assertFalse(auth()->user()->can('article.create'));

        $role->grantPermissionBySlug('article.create');

        $this->assertTrue($role->can('article.create'));
        $this->assertTrue(auth()->user()->fresh()->can('article.create'));

        $role->grantPermissionBySlug('article.xxx');
        $this->assertFalse($role->can('article.xxx'));
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
}
