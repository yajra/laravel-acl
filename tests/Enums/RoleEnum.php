<?php

namespace Yajra\Acl\Tests\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case REGISTERED = 'registered';
    case TEST_ADMIN = 'test-admin';
    case TEST_MANAGER = 'test-manager';
    case MODERATOR = 'moderator';
    case GUEST = 'guest';
}
