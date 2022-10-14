<?php

return [
    /**
     * User class used for ACL.
     */
    'user' => App\Models\User::class,

    /**
     * Role class used for ACL.
     */
    'role' => Yajra\Acl\Models\Role::class,

    /**
     * Permission class used for ACL.
     */
    'permission' => Yajra\Acl\Models\Permission::class,

    /**
     * Cache config.
     */
    'cache' => [
        'enabled' => true,
        'key' => 'permissions.policies',
    ],
];
