<?php

namespace Yajra\Acl\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $role = explode('|', $role);
        if (!auth()->user() || !auth()->user()->hasRole($role)) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => [
                        'status_code' => 401,
                        'code' => 'INSUFFICIENT_PERMISSIONS',
                        'description' => 'You are not authorized to access this resource.',
                    ],
                ], 401);
            }

            abort(401, 'You are not authorized to access this resource.');
        }

        return $next($request);
    }
}
