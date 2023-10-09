<?php

namespace App\Application\Dashboard;


use App\Domain\Role\Permissions;
use App\Domain\Role\Role;
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

class GenericController
{
    use HttpResponse;

    public function __construct(public LoggerInterface $logger, public RoleRepository $roleRepository) { }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function viewDashboard(Request $request, Response $response, Environment $twig): Response|Message
    {
        $response->getBody()->write($twig->render('dashboard.twig'));
        return $response->withHeader('Content-Type', 'text/html');
    }


}

