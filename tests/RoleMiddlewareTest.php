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
             ->assertSee('You are not authorized to access this resource.');
    }

    /** @test */
    public function it_can_access_role_protected_routes()
    {
        $this->actingAs($this->admin)->get('/')->assertSee('Pass');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->app['router']->get('/', function () {
            return 'Pass';
        })->middleware(['web', 'role:admin']);
    }
}
