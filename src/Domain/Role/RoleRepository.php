<?php

declare(strict_types=1);

namespace App\Domain\Role;

interface RoleRepository
{
    /**
     * @return Role[]
     */
    public function findAll(): array;

    public function find(int $roleId):bool|Role;

    public function delete(int $roleId):bool;

    public function create(Role $role):bool|string;

    public function update(Role $role):bool;

}
