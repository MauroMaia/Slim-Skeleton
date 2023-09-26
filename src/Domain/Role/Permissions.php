<?php

declare(strict_types=1);

namespace App\Domain\Role;

enum Permissions: string
{
    case ADMIN = 'admin';
    case GUEST = 'guest';
}