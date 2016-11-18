<?php

namespace Yajra\Acl\Directives;

use Illuminate\Contracts\Auth\Guard;

abstract class DirectiveAbstract
{
    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * IsRoleDirective constructor.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }
}
