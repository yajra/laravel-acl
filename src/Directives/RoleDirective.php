<?php

namespace Yajra\Acl\Directives;

class RoleDirective extends DirectiveAbstract
{
    /**
     * Is Role blade directive compiler.
     *
     * @param  string|array  $role
     * @return bool
     */
    public function handle($role): bool
    {
        if ($this->auth->check()) {
            return $this->auth->user()->hasRole($role);
        }

        return $role === 'guest';
    }
}
