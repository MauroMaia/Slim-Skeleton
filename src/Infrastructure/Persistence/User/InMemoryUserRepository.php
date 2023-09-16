<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;

class InMemoryUserRepository implements UserRepository
{
    /**
     * @var User[]
     */
    private array $users;

    /**
     * @param User[]|null $users
     */
    public function __construct(array $users = null)
    {
        $this->users = $users ?? [
            1 => new User(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new User(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new User(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new User(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new User(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->users);
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): User
    {
        if (!isset($this->users[$id])) {
            throw new UserNotFoundException();
        }

        return $this->users[$id];
    }

    /**
     * @param string $username
     * @return User
     */
    public function findByUsername(string $username): User
    {
        // TODO: Implement findByUsername() method.
    }

    /**
     * @param string $email
     * @return User
     */
    public function findByEmail(string $email): User
    {
        // TODO: Implement findByEmail() method.
    }

    /**
     * @param User $user
     * @param string $newHash
     * @return bool
     */
    public function updateUserPassword(User $user, string $newHash): bool
    {
        // TODO: Implement updateUserPassword() method.
    }

    /**
     * @param User $user
     * @param string $newHash
     * @return bool
     */
    public function updateUserRecoverPassword(User $user, string $newHash): bool
    {
        // TODO: Implement updateUserRecoverPassword() method.
    }

    /**
     * @param User $user
     * @return bool
     */
    public function add(User $user): bool
    {
        // TODO: Implement add() method.
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId): bool
    {
        // TODO: Implement delete() method.
    }
}
