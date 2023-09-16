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
    public function findById(int $id): User;

    /**
     * @param string $username
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function findByUsername(string $username): User;

    /**
     * @param string $email
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function findByEmail(string $email): User;

    public function updateUserPassword(User $user, string $newHash): bool;

    public function updateUserRecoverPassword(User $user, string $newHash): bool;

    public function add(User $user): bool;

    public function delete(int $userId):bool;

}
