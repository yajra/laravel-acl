<?php

namespace Yajra\Acl\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Yajra\Acl\Middleware\CanAtLeastMiddleware;
use Yajra\Acl\Tests\TestCase;

class CanAtLeastMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_access_permission_protected_routes()
    {
        Auth::login($this->admin);

        $middleware = new CanAtLeastMiddleware;

        $response = $middleware->handle(new Request(), fn () => 'Pass', 'create-article,non-existing-permission');

        $this->assertEquals('Pass', $response);
    }
}
