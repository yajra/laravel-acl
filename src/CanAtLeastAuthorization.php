<?php

namespace Yajra\Acl;

use Closure;

class CanAtLeastAuthorization
{
    const FORBIDDEN_STATUS_CODE = 403;

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
            abort(self::FORBIDDEN_STATUS_CODE, trans('laravel-acl::texts.forbidden'));
        }

        return $next($request);
    }
}
