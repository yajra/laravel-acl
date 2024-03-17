<?php

namespace Yajra\Acl\Directives;

class RoleDirective extends DirectiveAbstract
{
    /**
     * Is Role blade directive compiler.
     *
     * @param  string|string[]  $role
     */
    public function handle(string|array $role): bool
    {
        if ($this->auth->user() && method_exists($this->auth->user(), 'hasRole')) {
            return $this->auth->user()->hasRole($role);
        }

        return $role === 'guest';
    }
}
