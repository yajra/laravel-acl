<?php

namespace Yajra\Acl;

use Closure;

class CanAtLeastAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        $abilities = explode(',', $permissions);

        if (! auth()->check() || ! auth()->user()->canAtLeast($abilities)) {
            abort(403, trans('texts.forbidden'));
        }

        return $next($request);
    }
}
