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
        $result = $this->db->runWithParams("select * from user;", []);

        foreach ($result as $index => $line) {
            $result[$index] = new User(
                $line['id'],
                $line['username'],
                $line['firstName'],
                $line['lastName'],
                $line['password'],
                $line['recoverPassword'],
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
            $result[0]['recoverPassword'],
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
            $result[0]['recoverPassword'],
            $result[0]['email'],
            new \DateTime($result[0]['created_at']),
            new \DateTime($result[0]['updated_at']),
        );
    }

    public function findUserByEmail(string $email): User
    {
        $result = $this->db->runWithParams("select * from user where email = ? limit 1;", [$email]);

        if (!isset($result[0])) {
            throw new UserNotFoundException();
        }

        return new User(
            $result[0]['id'],
            $result[0]['username'],
            $result[0]['firstName'],
            $result[0]['lastName'],
            $result[0]['password'],
            $result[0]['recoverPassword'],
            $result[0]['email'],
            new \DateTime($result[0]['created_at']),
            new \DateTime($result[0]['updated_at']),
        );
    }

    public function updateUserPassword(User $user, string $newHash): bool
    {
        $result = $this->db->runWithParams("update user set password = ? where id = ?;", [$newHash, $user->id]);

        if (!isset($result[0])) {
            return false;
        }
        return true;
    }

    public function updateUserRecoverPassword(User $user, string $newHash): bool
    {
        $result = $this->db->runWithParams(
            "update user set recoverPassword = ?, password = '' where id = ?;",
            [
                $newHash,
                $user->id
            ]
        );

        if (!isset($result[0])) {
            return false;
        }
        return true;
    }

   /* public function getUserPermissions(User $user): bool
    {
        $result = $this->db->runWithParams(
            "SELECT MAX(IF(permission = 'ADMIN', 1, 0)) AS admin,
		                    MAX(IF(permission = 'LIST_USER', 1, 0)) AS list_users,
		                    MAX(IF(permission = 'EDIT_USER', 1, 0)) AS edit_user
                    FROM user_permissions
                    WHERE userid = ?
                    GROUP BY userid",
            [$user->id]
        );

        if (!isset($result[0])) {
            return false;
        }
        return true;
    }*/
}
