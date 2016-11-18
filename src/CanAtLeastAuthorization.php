<?php

namespace Yajra\Acl;

class CanAtLeastAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  callable $next
     * @param  string $permissions
     * @return mixed
     */
    public function handle($request, callable $next, $permissions)
    {
        $abilities = explode(',', $permissions);

        if (! auth()->check() || ! auth()->user()->canAtLeast($abilities)) {
            abort(403, 'You are not allowed to view this content!');
        }

        return $next($request);
    }
}
