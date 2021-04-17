<?php

namespace Yajra\Acl\Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Models\Role;
use Yajra\Acl\Traits\HasRoleAndPermission;

class UserWithPermission extends Authenticatable
{
    use HasRoleAndPermission;

    protected $table = 'users';

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id')->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')->withTimestamps();
    }
}
