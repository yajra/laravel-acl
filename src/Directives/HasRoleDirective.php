<?php

namespace Yajra\Acl\Directives;

class HasRoleDirective extends DirectiveAbstract
{
    /**
     * Handle hasRole directive.
     *
     * @param string|array $roles
     * @return bool
     */
    public function handle($roles)
    {
        if ($this->auth->check()) {
            return $this->auth->user()->hasRole($roles);
        }

        return false;
    }
}
