<?php

declare(strict_types=1);

namespace App\Domain\Role;

interface RoleRepository
{
    /**
     * @return Role[]
     */
    public function findAll(): array;

    public function delete(int $roleId):bool;
}
