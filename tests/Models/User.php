<?php

namespace Yajra\Acl\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Yajra\Acl\Traits\HasRoleAndPermission;

class User extends Authenticatable
{
    use HasRoleAndPermission;

    protected $guarded = [];
}
