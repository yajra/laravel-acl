<?php

namespace Yajra\Acl;

use Yajra\Acl\Models\Role;

class CanAtLeastDirective
{
    /**
     * Can at least blade directive compiler.
     *
     * @param string|array $permissions
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle($permissions)
    {
        if (auth()->check()) {
            return auth()->user()->canAtLeast((array) $permissions);
        } else {
            $guest = Role::whereSlug('guest')->first();
            if ($guest) {
                return $guest->canAtLeast((array) $permissions);
            }
        }

        return false;
    }
}
