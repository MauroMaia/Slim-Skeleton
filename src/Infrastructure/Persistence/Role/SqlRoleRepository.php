<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Role;

use App\Domain\Role\RoleRepository;
use App\Domain\User\User;
use App\Infrastructure\Persistence\DatabaseConnection;
use Exception;

readonly class SqlRoleRepository implements RoleRepository
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
        $result = $this->db->runWithParams("SELECT * FROM user;", []);

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
                new \DateTime($line['created_at']),
                new \DateTime($line['updated_at'])
            );
        }
        return $result;
    }


    public function delete(int $roleId): bool
    {
        if($roleId == 1) return false;
        $result = $this->db->runWithParams("DELETE FROM role WHERE id = ?;", [$roleId]);

        return true;
    }
}
