<?php

namespace Yajra\Acl\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Yajra\Acl\Traits\HasRole;

class User extends Authenticatable
{
    use HasRole;
}
