<?php

namespace Yajra\Acl\Tests\Http;

use Orchestra\Testbench\Foundation\Http\Kernel as HttpKernel;
use Yajra\Acl\Middleware\CanAtLeastMiddleware;
use Yajra\Acl\Middleware\PermissionMiddleware;
use Yajra\Acl\Middleware\RoleMiddleware;

final class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        'role' => RoleMiddleware::class,
        'permission' => PermissionMiddleware::class,
        'canAtLeast' => CanAtLeastMiddleware::class,
    ];
}
