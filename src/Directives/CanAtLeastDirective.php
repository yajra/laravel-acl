<?php

namespace Yajra\Acl\Directives;

use Yajra\Acl\Models\Role;

class CanAtLeastDirective extends DirectiveAbstract
{
    /**
     * Can at least blade directive compiler.
     *
     * @param  string|array  $permissions
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle($permissions): bool
    {
        if ($this->auth->check()) {
            // @phpstan-ignore-next-line
            return $this->auth->user()->canAtLeast((array) $permissions);
        }

        $guest = Role::whereSlug('guest')->first();
        if ($guest) {
            return $guest->canAtLeast((array) $permissions);
        }

        return false;
    }
}
