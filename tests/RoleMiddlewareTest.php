<?php

namespace Yajra\Acl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

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
        $this->actingAs($this->admin)->getJson('/')->assertSee('Pass');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->app['router']->get('/', function () {
            return 'Pass';
        })->middleware(['role:admin']);
    }
}
