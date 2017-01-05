<?php

namespace Yajra\Acl\Middleware;

use Closure;

class CanAtLeastMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  array|string $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        $abilities = is_array($permissions) ? $permissions : explode(',', $permissions);

        if (! auth()->check() || ! auth()->user()->canAtLeast($abilities)) {
            abort(403, 'You are not allowed to view this content!');
        }

        return $next($request);
    }
}
