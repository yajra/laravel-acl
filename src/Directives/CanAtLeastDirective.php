<?php

namespace Yajra\Acl\Directives;

use Yajra\Acl\Models\Role;

class CanAtLeastDirective extends DirectiveAbstract
{
    /**
     * Can at least blade directive compiler.
     *
     * @param  string|string[]  $permissions
     */
    public function handle(string|array $permissions): bool
    {
        if ($this->auth->user() && method_exists($this->auth->user(), 'canAtLeast')) {
            return $this->auth->user()->canAtLeast((array) $permissions);
        }

        $guest = Role::whereSlug('guest')->first();
        if ($guest) {
            return $guest->canAtLeast((array) $permissions);
        }

        return false;
    }
}
