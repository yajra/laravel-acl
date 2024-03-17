<?php

namespace Yajra\Acl\Directives;

class RoleDirective extends DirectiveAbstract
{
    /**
     * Is Role blade directive compiler.
     */
    public function handle(string|array $role): bool
    {
        if ($this->auth->check()) {
            // @phpstan-ignore-next-line
            return $this->auth->user()->hasRole($role);
        }

        return $role === 'guest';
    }
}
