<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\DatabaseConnection;
use Exception;
use http\Exception\InvalidArgumentException;

readonly class SqlUserRepository implements UserRepository
{

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(private DatabaseConnection $db) { }

    /**
     * @param User $user
     *
     * @return User
     * @throws InvalidArgumentException
     */
    public function add(User $user): User
    {
        $result = $this->db->insert(
            "INSERT INTO user(username, firstName, lastName, email, password, jobTitle)
                    VALUES (?,?,?,?,?,?);",
            [
                $user->getUsername(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->email,
                $user->password,
                $user->jobTitle
            ]);

        if (!isset($result[0])) throw new InvalidArgumentException("Failed to insert user");

        $user->id = (int)$result;

        $result = $this->db->insert(
            "INSERT INTO user_role(user_id, role_id) VALUES (?,?);",
            [
                $user->id,
                $user->roleId
            ]);

        if ($result === false) throw new InvalidArgumentException("Failed to insert user role");

        return $user;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function findAll(): array
    {
        $result = $this->db->runWithParams(
            "SELECT user.*, user_role.role_id  
                    FROM user
                        INNER JOIN user_role ON user.id = user_role.user_id
                    WHERE user.deleted = false;"
        );

        foreach ($result as $index => $line) {
            $result[$index] = new User(
                $line['id'],
                $line['username'],
                $line['firstName'],
                $line['lastName'],
                $line['password'],
                $line['recoverPassword'],
                $line['email'],
                $line['jobTitle'],
                $line['role_id'],
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
    public function findById(int $id): User
    {
        $result = $this->db->runWithParams(
            "SELECT user.*, user_role.role_id 
                FROM user
                    INNER JOIN user_role ON user.id = user_role.user_id
                WHERE user.deleted = false AND user.id = ?;",
            [$id]
        );

        if ($result === false || !isset($result[0])) throw new UserNotFoundException();

        return new User(
            $result[0]['id'],
            $result[0]['username'],
            $result[0]['firstName'],
            $result[0]['lastName'],
            $result[0]['password'],
            $result[0]['recoverPassword'],
            $result[0]['email'],
            $result[0]['jobTitle'],
            $result[0]['role_id'],
            new \DateTime($result[0]['created_at']),
            new \DateTime($result[0]['updated_at']),
        );
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function findByUsername(string $username): User
    {
        $result = $this->db->runWithParams(
            "SELECT user.*, user_role.role_id  
                FROM user 
                    INNER JOIN user_role ON user.id = user_role.user_id
                WHERE user.deleted = false AND username = ? LIMIT 1;",
            [$username]
        );

        if (!isset($result[0])) throw new UserNotFoundException();

        return new User(
            $result[0]['id'],
            $result[0]['username'],
            $result[0]['firstName'],
            $result[0]['lastName'],
            $result[0]['password'],
            $result[0]['recoverPassword'],
            $result[0]['email'],
            $result[0]['jobTitle'],
            $result[0]['role_id'],
            new \DateTime($result[0]['created_at']),
            new \DateTime($result[0]['updated_at']),
        );
    }

    public function findByEmail(string $email): User
    {
        $result = $this->db->runWithParams(
            "SELECT user.*, user_role.role_id  
                FROM user 
                    INNER JOIN user_role ON user.id = user_role.user_id
                WHERE user.deleted = false AND user.email = ? LIMIT 1;",
            [$email]
        );

        if (!isset($result[0])) throw new UserNotFoundException();

        return new User(
            $result[0]['id'],
            $result[0]['username'],
            $result[0]['firstName'],
            $result[0]['lastName'],
            $result[0]['password'],
            $result[0]['recoverPassword'],
            $result[0]['email'],
            $result[0]['jobTitle'],
            $result[0]['role_id'],
            new \DateTime($result[0]['created_at']),
            new \DateTime($result[0]['updated_at']),
        );
    }

    public function updateUserPassword(User $user, string $newHash): bool
    {
        $result = $this->db->runWithParams(
            "UPDATE user SET password = ? WHERE deleted = false AND id = ?;",
            [$newHash, $user->id]
        );

        if ($result === false) return false;

        return true;
    }

    public function updateUserRecoverPassword(User $user, string $newHash): bool
    {
        $result = $this->db->runWithParams(
            "UPDATE user SET recoverPassword = ?, password = '' WHERE deleted = false AND id = ?;",
            [
                $newHash,
                $user->id
            ]
        );

        if (!isset($result[0])) return false;

        return true;
    }

    public function delete(int $userId): bool
    {
        if($userId == 1) return false;

        $result = $this->db->runWithParams(
            "UPDATE user set deleted = true WHERE id = ?;",
            [$userId]
        );

        return $result !== false;
    }

   public function getUserPermissions(int $userId): array
    {
        $result = $this->db->runWithParams(
            "
                SELECT user_role.user_id,
                   IF(permission = 'ADMIN', rp.enabled, 0) AS admin,
                   CASE
                       WHEN permission = 'ADMIN' THEN rp.enabled
                       WHEN permission = 'LIST_USER' THEN rp.enabled
                       ELSE 0
                   END AS list_user
                FROM  user_role
                    INNER JOIN user                 ON user_role.role_id = user.id
                    INNER JOIN role                 ON user_role.role_id = role.id
                    INNER JOIN role_permission rp   ON role.id = rp.role_id
                WHERE user.deleted = false AND user_role.user_id = ?
                GROUP BY user_role.user_id",
            [$userId]
        );

        $result[0]['guest'] = 1;

        return $result[0];
    }

}
