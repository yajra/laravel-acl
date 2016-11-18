<?php

namespace Yajra\Acl\Directives;

use Yajra\Acl\Models\Role;

class CanAtLeastDirective extends DirectiveAbstract
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
        if ($this->auth->check()) {
            return $this->auth->user()->canAtLeast((array) $permissions);
        } else {
            $guest = Role::whereSlug('guest')->first();
            if ($guest) {
                return $guest->canAtLeast((array) $permissions);
            }
        }

        return false;
    }
}
