<?php

declare(strict_types=1);

namespace App\Domain\Role;

enum Permissions: string
{
    // With this permission can access every thing
    case ADMIN = 'admin';

    // With this permission can only access public information
    case GUEST = 'guest';

    // With this permission can see/edit user and role resources
    case USER_MANAGEMENT = 'user_management';

    // With this permission can see/edit app resources (not allowed to access or edit user/role resources)
    case EDITOR = 'editor';

    // With this permission can see app resources
    case READ_ONLY = 'read_only';
}