<?php

namespace Yajra\Acl\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanAtLeastMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, array|string $permissions)
    {
        $abilities = is_array($permissions) ? $permissions : explode(',', $permissions);

        if (! auth()->check() || ! auth()->user()->canAtLeast($abilities)) {
            abort(403, 'You are not allowed to view this content!');
        }

        return $next($request);
    }
}
