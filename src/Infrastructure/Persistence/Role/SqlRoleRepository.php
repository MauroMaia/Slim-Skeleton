<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Role;

use App\Domain\Role\Role;
use App\Domain\Role\RoleRepository;
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
        $result = $this->db->runWithParams(
            "SELECT * from role r
                        left join slim.role_permission rp on r.id = rp.role_id
                        where rp.enabled = true || rp.enabled is null", []);

        $roles =[];

        foreach ($result as $index => $line)
        {
            //continue;
            if(!array_key_exists($line['id'], $roles))
            {
                $roles[$line['id']] = new Role(
                    $line['id'],
                    $line['name'],
                    [$line['permission']],
                    is_null($line['created_at'])?new \DateTime():new \DateTime($line['created_at']),
                    is_null($line['updated_at'])?new \DateTime():new \DateTime($line['updated_at']),
                );
            }else{
                $roles[$line['id']] = new Role(
                    $line['id'],
                    $line['name'],
                    array_merge($roles[$line['id']]->permissions,[$line['permission']]),
                    new \DateTime($line['created_at']),
                    new \DateTime($line['updated_at'])
                );
            }
        }
        return array_values($roles);
    }


    public function delete(int $roleId): bool
    {
        if($roleId == 1) return false;
        $result = $this->db->runWithParams("DELETE FROM role WHERE id = ?;", [$roleId]);

        return true;
    }
}
