<?php

namespace Yajra\Acl\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanAtLeastMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string|string[]  $permissions
     */
    public function handle(Request $request, Closure $next, array|string $permissions): mixed
    {
        $abilities = is_array($permissions) ? $permissions : explode(',', $permissions);

        if ($this->deniesAccessUsing($abilities)) {
            abort(403, 'You are not allowed to view this content!');
        }

        return $next($request);
    }

    /**
     * @param  string[]  $abilities
     */
    protected function deniesAccessUsing(array $abilities): bool
    {
        return ! auth()->user()
            || (method_exists(auth()->user(), 'canAtLeast') && ! auth()->user()->canAtLeast($abilities));
    }
}
