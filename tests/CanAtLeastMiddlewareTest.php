<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Acl\Middleware\CanAtLeastMiddleware;

class CanAtLeastMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_access_permission_protected_routes()
    {
        Auth::login($this->admin);

        $middleware = new CanAtLeastMiddleware;

        $response = $middleware->handle(new Request(), function () {
            return 'Pass';
        }, 'create-article,non-existing-permission');

        $this->assertEquals('Pass', $response);
    }
}
