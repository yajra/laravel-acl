<?php

namespace Yajra\Acl\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        $role = explode('|', $role);
        if (! $request->user() || ! $request->user()->hasRole($role)) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => [
                        'status_code' => 401,
                        'code'        => 'INSUFFICIENT_PERMISSIONS',
                        'description' => 'You are not authorized to access this resource.',
                    ],
                ], 401);
            }

            return abort(401, 'You are not authorized to access this resource.');
        }

        return $next($request);
    }
}
