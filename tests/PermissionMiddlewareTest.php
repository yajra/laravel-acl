<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Acl\GateRegistrar;
use Yajra\Acl\Middleware\PermissionMiddleware;

class PermissionMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_access_permission_protected_routes()
    {
        Auth::login($this->admin);

        $middleware = new PermissionMiddleware;

        $response   = $middleware->handle(new Request(), function () {
            return 'Pass';
        }, 'create-article');

        $this->assertEquals('Pass', $response);
    }
}
