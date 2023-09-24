<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserNotFoundException;
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

class UserController
{
    use HttpResponse;

    public function __construct(public LoggerInterface $logger, public UserRepository $userRepository) { }

    /**
     * @param Request     $request
     * @param Response    $response
     * @param Environment $twig
     *
     * @return Response|Message
     * @throws UserNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function viewUserProfile(Request $request, Response $response, Environment $twig): Response|Message
    {
        $userId = (int)$request->getAttribute('id');
        $user = $this->userRepository->findById($userId);

        $response->getBody()->write($twig->render('pages/admin/edit-user.twig', ["user" => $user]));
        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Environment $twig
     *
     * @return Response|Message
     */
    public function deleteUserProfile(Request $request, Response $response, Environment $twig): Response|Message
    {
        $userId = (int)$request->getAttribute('id');
        if($this->userRepository->delete($userId)){
            return $response->withStatus(200);
        }
        return $response->withStatus(400);
    }

}
