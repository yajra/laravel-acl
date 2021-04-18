<?php

namespace Yajra\Acl\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanAtLeastMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  array|string  $permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permissions)
    {
        $abilities = is_array($permissions) ? $permissions : explode(',', $permissions);

        if (!auth()->check() || !auth()->user()->canAny($abilities)) {
            abort(403, 'You are not allowed to view this content!');
        }

        return $next($request);
    }
}
