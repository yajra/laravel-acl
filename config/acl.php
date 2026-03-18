<?php

use App\Models\User;
use Yajra\Acl\Models\Permission;
use Yajra\Acl\Models\Role;

return [
    /**
     * User class used for ACL.
     */
    'user' => User::class,

    /**
     * Role class used for ACL.
     */
    'role' => Role::class,

    /**
     * Permission class used for ACL.
     */
    'permission' => Permission::class,

    /**
     * Cache config.
     */
    'cache' => [
        'enabled' => true,
        'key' => 'permissions.policies',
    ],
];
