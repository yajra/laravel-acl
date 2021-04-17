<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Acl\GateRegistrar;
use Yajra\Acl\Middleware\PermissionMiddleware;
use Yajra\Acl\Tests\Models\UserWithPermission;

class HasRoleAndPermissionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_access_permission_protected_routes()
    {
        resolve(GateRegistrar::class)->register();

        /** @var UserWithPermission $user */
        $user = UserWithPermission::forceCreate([
            'name' => 'User with Permissions',
            'email' => 'user-permission@example.com',
        ]);

        Auth::login($user);

        $middleware = new PermissionMiddleware;

        $this->expectExceptionMessage('You are not authorized to access this resource.');
        $response = $middleware->handle(new Request(), function () {
            return 'Pass';
        }, 'article.create');

        $user->grantPermissionBySlug('article.create');

        $this->assertEquals('Pass', $response);
    }
}
