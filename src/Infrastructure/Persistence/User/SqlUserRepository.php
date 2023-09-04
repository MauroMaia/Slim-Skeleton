<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\DatabaseConnection;
use Exception;

readonly class SqlUserRepository implements UserRepository
{

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(private DatabaseConnection $db) { }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function findAll(): array
    {
        $result = $this->db->runWithParams("select * from user;",[]);

        foreach ($result as $index => $line){
            $result[$index] = new User(
                $line['id'],
                $line['username'],
                $line['firstName'],
                $line['lastName'],
                $line['password'],
                $line['email'],
                new \DateTime($line['created_at']),
                new \DateTime($line['updated_at'])
            );
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function findUserById(int $id): User
    {
        $result = $this->db->runWithParams("select * from user where id = ?;", [$id]);

        if (!isset($result[0])) {
            throw new UserNotFoundException();
        }

        return new User(
            $result[0]['id'],
            $result[0]['username'],
            $result[0]['firstName'],
            $result[0]['lastName'],
            $result[0]['password'],
            $result[0]['email'],
            new \DateTime($result[0]['created_at']),
            new \DateTime($result[0]['updated_at']),
        );
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function findUserByUsername(string $username): User
    {
        $result = $this->db->runWithParams("select * from user where username = ? limit 1;", [$username]);

        if (!isset($result[0])) {
            throw new UserNotFoundException();
        }

        return new User(
            $result[0]['id'],
            $result[0]['username'],
            $result[0]['firstName'],
            $result[0]['lastName'],
            $result[0]['password'],
            $result[0]['email'],
            new \DateTime($result[0]['created_at']),
            new \DateTime($result[0]['updated_at']),
        );
    }
}
