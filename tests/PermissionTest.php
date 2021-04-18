<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Models\Role;

class PermissionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_create_resource_permissions()
    {
        $permissions = Permission::createResource('Users');
        $this->assertCount(5, $permissions);

        $this->assertTrue(Permission::findBySlug('users.viewAny')->exists());
        $this->assertTrue(Permission::findBySlug('users.view')->exists());
        $this->assertTrue(Permission::findBySlug('users.create')->exists());
        $this->assertTrue(Permission::findBySlug('users.update')->exists());
        $this->assertTrue(Permission::findBySlug('users.delete')->exists());
    }

    /** @test */
    public function it_refreshes_permissions_cache()
    {
        $permissions = cache('permissions.policies');
        $this->assertCount(5, $permissions);

        Permission::createResource('Posts');

        $permissions = cache('permissions.policies');
        $this->assertCount(10, $permissions);
    }
}
