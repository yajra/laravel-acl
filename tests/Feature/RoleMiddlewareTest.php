<?php

namespace Yajra\Acl\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Yajra\Acl\Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    #[Test]
    public function it_cannot_access_protected_routes()
    {
        $this->get('/')->assertStatus(401);
    }

    #[Test]
    public function a_user_cannot_access_admin_routes()
    {
        $this->actingAs($this->user)
            ->get('/')
            ->assertSee('Unauthorized');
    }

    #[Test]
    public function it_can_access_role_protected_routes()
    {
        $this->actingAs($this->admin)->get('/')->assertSee('Pass');
    }

    #[Test]
    public function it_can_allows_access_on_comma_separated_roles()
    {
        $this->actingAs($this->admin)->get('/comma')->assertSee('Pass');
    }

    #[Test]
    public function it_can_allows_access_on_pipe_separated_roles()
    {
        $this->actingAs($this->admin)->get('/pipe')->assertSee('Pass');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->get('/', fn() => 'Pass')->middleware(['web', 'role:admin']);

        $this->app['router']->get('/comma', fn() => 'Pass')->middleware(['web', 'role:admin,user']);

        $this->app['router']->get('/pipe', fn() => 'Pass')->middleware(['web', 'role:admin|user']);
    }
}
