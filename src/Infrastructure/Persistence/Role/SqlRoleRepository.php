<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Role;

use App\Domain\Role\Role;
use App\Domain\Role\RoleRepository;
use App\Infrastructure\Persistence\DatabaseConnection;
use Exception;
use Psr\Log\LoggerInterface;

readonly class SqlRoleRepository implements RoleRepository
{

    /**
     * @param DatabaseConnection $db
     * @param LoggerInterface $logger
     */
    public function __construct(private DatabaseConnection $db, public LoggerInterface $logger) { }


    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function findAll(): array
    {
        $result = $this->db->runWithParams(
            "SELECT * FROM role
                        LEFT JOIN role_permission  ON role.id = role_permission.role_id
                        WHERE role.deleted = false AND
                            (
                                role_permission.enabled = true ||
                                role_permission.enabled is null
                            )"
        );

        $roles =[];

        foreach ($result as $line)
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

    /**
     * @throws Exception
     */
    public function find(int $roleId): bool|Role
    {
        $result = $this->db->runWithParams(
            "SELECT * FROM role
                        LEFT JOIN role_permission on role.id = role_permission.role_id
                        WHERE role.deleted = false AND
                            role.id = ? AND
                            ( 
                                role_permission.enabled = true || 
                                role_permission.enabled is null
                            );",
            [$roleId]
        );

        if($result === false) return false;

        $roles = [];

        foreach ($result as $line)
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
        return $roles[$roleId];
    }

    public function delete(int $roleId): bool
    {
        if($roleId <= 3) return false;

        $this->db->runWithParams(
            "UPDATE role SET deleted = true WHERE id = ?;",
            [$roleId]
        );

        return true;
    }

    public function create(Role $role): bool|string
    {
        $result = $this->db->insert(
            "INSERT into role (name) values (?)", [$role->name]);
        $this->logger->debug("added new role",['name'=>$role->name,'role_inserted'=>$result]);

        if($result !== false){
            foreach ($role->permissions as $permission)
            {
                $id = $this->db->insert(
                    "INSERT into role_permission (role_id, permission, enabled) values (?,?,?)",
                    [$result, $permission, true]
                );
                $this->logger->debug("added new permission to role",['permission'=>$permission,'result'=>$id]);

                if($id === false) return false;
            }
        }

        return $result;
    }

    public function update(Role $role): bool
    {
        // update role
        $result = $this->db->runWithParams(
            "UPDATE role SET name = ? WHERE id = ?", [$role->name, $role->id]);
        $this->logger->debug("added new role",['name'=>$role->name,'role_inserted'=>$result]);

        if($result === false) return false;

        $result = $this->db->runWithParams(
            "UPDATE role_permission SET enabled = false WHERE role_id = ?", [$role->id]);
        $this->logger->debug("Disabled all role_permission for id",['id'=>$role->id,'role_inserted'=>$result]);

        foreach ($role->permissions as $permission)
        {
            $this->db->insert(
                "INSERT INTO role_permission (role_id, permission, enabled) VALUES (?,?,?)   
                            ON DUPLICATE KEY UPDATE enabled = ?",
                [$role->id, $permission, true,true]
            );
            $this->logger->debug("added new permission to role",['permission'=>$permission]);
        }

        return $result !== false;
    }
}
