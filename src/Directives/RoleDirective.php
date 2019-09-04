<?php

namespace Yajra\Acl\Directives;

class RoleDirective extends DirectiveAbstract
{
    /**
     * Is Role blade directive compiler.
     *
     * @param string $role
     * @return bool
     */
    public function handle($role)
    {
        if ($this->auth->check()) {
            return $this->auth->user()->isRole($role);
        }

        return $role === 'guest';
    }
}
