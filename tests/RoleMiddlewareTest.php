<?php

namespace Yajra\Acl\Tests;

class RoleMiddlewareTest extends TestCase
{
    /** @test */
    public function it_cannot_access_protected_routes()
    {
        $this->get('/')->assertStatus(401);
    }

    /** @test */
    public function a_user_cannot_access_admin_routes()
    {
        $this->actingAs($this->user)
            ->get('/')
            ->assertSee('Unauthorized');
    }

    /** @test */
    public function it_can_access_role_protected_routes()
    {
        $this->actingAs($this->admin)->get('/')->assertSee('Pass');
    }

    /** @test */
    public function it_can_allows_access_on_comma_separated_roles()
    {
        $this->actingAs($this->admin)->get('/comma')->assertSee('Pass');
    }

    /** @test */
    public function it_can_allows_access_on_pipe_separated_roles()
    {
        $this->actingAs($this->admin)->get('/pipe')->assertSee('Pass');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->get('/', function () {
            return 'Pass';
        })->middleware(['web', 'role:admin']);

        $this->app['router']->get('/comma', function () {
            return 'Pass';
        })->middleware(['web', 'role:admin,user']);

        $this->app['router']->get('/pipe', function () {
            return 'Pass';
        })->middleware(['web', 'role:admin|user']);
    }
}
