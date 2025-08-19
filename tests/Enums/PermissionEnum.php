<?php

namespace Yajra\Acl\Tests\Enums;

enum PermissionEnum: string
{
    case CREATE_POST = 'create-post';
    case EDIT_POST = 'edit-post';
    case DELETE_POST = 'delete-post';
    case VIEW_ADMIN = 'view-admin';
    case MANAGE_USERS = 'manage-users';
}
