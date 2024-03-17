<?php

namespace Yajra\Acl\Directives;

use Illuminate\Contracts\Auth\Guard;

abstract class DirectiveAbstract
{
    /**
     * IsRoleDirective constructor.
     */
    public function __construct(protected Guard $auth)
    {
    }
}
