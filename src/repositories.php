<?php

declare(strict_types=1);

use App\Domain\Role\RoleRepository;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Role\SqlRoleRepository;
use App\Infrastructure\Persistence\User\SqlUserRepository;
use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder)
{
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions(
        [
            //UserRepository::class => autowire(InMemoryUserRepository::class),
            UserRepository::class => autowire(SqlUserRepository::class),
            RoleRepository::class => autowire(SqlRoleRepository::class),
        ]
    );
};
