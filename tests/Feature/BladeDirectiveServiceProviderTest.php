<?php

namespace Yajra\Acl\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Yajra\Acl\Directives\CanAtLeastDirective;
use Yajra\Acl\Directives\RoleDirective;
use Yajra\Acl\Tests\Enums\RoleEnum;
use Yajra\Acl\Tests\TestCase;

class BladeDirectiveServiceProviderTest extends TestCase
{
    public function test_role_directive_can_be_resolved_from_container()
    {
        // This is the core test that verifies our fix for the original issue:
        // "Target class [RoleDirective] does not exist"

        $directive = app('laravel-acl.directives.role');

        $this->assertInstanceOf(RoleDirective::class, $directive);
    }

    public function test_can_at_least_directive_can_be_resolved_from_container()
    {
        $directive = app('laravel-acl.directives.canAtLeast');

        $this->assertInstanceOf(CanAtLeastDirective::class, $directive);
    }

    public function test_blade_directives_service_container_bindings()
    {
        // Verify we can resolve the directive classes (which proves they're properly available)
        $roleDirective = app(RoleDirective::class);
        $canAtLeastDirective = app(CanAtLeastDirective::class);

        $this->assertInstanceOf(RoleDirective::class, $roleDirective);
        $this->assertInstanceOf(CanAtLeastDirective::class, $canAtLeastDirective);

        // Verify we can resolve multiple instances
        $directive1 = app(RoleDirective::class);
        $directive2 = app(RoleDirective::class);

        $this->assertInstanceOf(RoleDirective::class, $directive1);
        $this->assertInstanceOf(RoleDirective::class, $directive2);
    }

    public function test_role_directive_functionality_with_authenticated_user()
    {
        Auth::login($this->admin);

        $roleDirective = app(RoleDirective::class);

        // Test with string
        $resultString = $roleDirective->handle('admin');

        // Test with enum value
        $resultEnum = $roleDirective->handle(RoleEnum::ADMIN->value);

        $this->assertTrue($resultString);
        $this->assertTrue($resultEnum);
        $this->assertEquals($resultString, $resultEnum);
    }

    public function test_role_directive_functionality_with_guest_user()
    {
        // No user logged in
        Auth::logout();

        $roleDirective = app(RoleDirective::class);

        // Test guest role
        $guestResult = $roleDirective->handle('guest');
        $nonGuestResult = $roleDirective->handle('admin');

        $this->assertTrue($guestResult);
        $this->assertFalse($nonGuestResult);
    }

    public function test_role_directive_handles_array_roles()
    {
        Auth::login($this->admin);

        $roleDirective = app(RoleDirective::class);

        // Test with array of roles
        $result = $roleDirective->handle(['admin', 'moderator']);

        $this->assertTrue($result);
    }

    public function test_service_provider_registration_fix_prevents_binding_resolution_exception()
    {
        // This test specifically ensures that the service provider fix works
        // Before the fix, this would throw: "Target class [RoleDirective] does not exist"

        try {
            $directive = app(RoleDirective::class);
            $canAtLeastDirective = app(CanAtLeastDirective::class);

            // If we reach here without exception, the fix worked
            $this->assertInstanceOf(RoleDirective::class, $directive);
            $this->assertInstanceOf(CanAtLeastDirective::class, $canAtLeastDirective);
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            $this->fail('Service provider fix failed. Directives could not be resolved: '.$e->getMessage());
        }
    }
}
