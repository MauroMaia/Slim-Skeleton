<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserRepository
{
    /**
     * @return User[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserById(int $id): User;

    /**
     * @param string $username
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserByUsername(string $username): User;
}
