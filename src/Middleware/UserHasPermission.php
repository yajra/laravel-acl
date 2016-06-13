<?php

namespace Yajra\Acl\Middleware;

use Closure;

class UserHasPermission
{
    const INSUFFICIENT_PERMISSIONS_STATUS_CODE = 401;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (! $request->user() || ! $request->user()->can($permission)) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => [
                        'status_code' => self::INSUFFICIENT_PERMISSIONS_STATUS_CODE,
                        'code'        => 'INSUFFICIENT_PERMISSIONS',
                        'description' => trans('texts.unauthorized'),
                    ],
                ], self::INSUFFICIENT_PERMISSIONS_STATUS_CODE);
            }

            return abort(self::INSUFFICIENT_PERMISSIONS_STATUS_CODE, trans('texts.unauthorized'));
        }

        return $next($request);
    }
}
