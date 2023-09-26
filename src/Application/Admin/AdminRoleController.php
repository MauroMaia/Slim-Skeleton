<?php

namespace App\Application\Admin;


use App\Domain\Role\Permissions;
use App\Domain\User\UserRepository;
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

    public function __construct(public LoggerInterface $logger, public UserRepository $userRepository) { }



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



}

