<?php

namespace Yajra\Acl\Tests\Http;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Orchestra\Testbench\Http\Kernel as OrchestraKernel;
use Yajra\Acl\Middleware\CanAtLeastMiddleware;
use Yajra\Acl\Middleware\PermissionMiddleware;
use Yajra\Acl\Middleware\RoleMiddleware;

class Kernel extends OrchestraKernel
{
    protected $routeMiddleware = [
        'auth'       => Authenticate::class,
        'bindings'   => SubstituteBindings::class,
        'can'        => Authorize::class,
        'role'       => RoleMiddleware::class,
        'permission' => PermissionMiddleware::class,
        'canAtLeast' => CanAtLeastMiddleware::class,
    ];
}
