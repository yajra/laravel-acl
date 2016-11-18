<?php

namespace Yajra\Acl\Directives;

class IsRoleDirective extends DirectiveAbstract
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
        } else {
            if ($role === 'guest') {
                return true;
            }
        }

        return false;
    }
}
