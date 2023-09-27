<?php

namespace App\Application\Admin;


use App\Domain\Role\Permissions;
use App\Domain\Role\RoleRepository;
use App\Infrastructure\Slim\HttpResponse;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Message;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AdminRoleController
{
    use HttpResponse;

    public function __construct(public LoggerInterface $logger, public RoleRepository $roleRepository) { }



    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function viewRoleList(Request $request, Response $response, Environment $twig): Response|Message
    {

        $response->getBody()->write($twig->render('pages/admin/list-roles.twig', [
            "permissions"=>array_column(Permissions::cases(), 'name')
        ]));
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function apiRolesList(Request $request, Response $response): Response|Message
    {
        $roles=$this->roleRepository->findAll();

        $response->getBody()->write(json_encode((object)[
            "permissions"=>array_column(Permissions::cases(), 'name'),
            "roles" => $roles
        ]));
        return $response->withHeader('Content-Type', 'text/html');
    }
}

