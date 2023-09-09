<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\Slim\HttpResponse;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ListUsersController
{
    use HttpResponse;

    public function __construct(public LoggerInterface $logger, public UserRepository $userRepository) { }

    public function list(Request $request, Response $response): Response
    {
        $users = $this->userRepository->findAll();

        $this->logger->info("Users list was viewed.");

        return $this->respondWithData($response, $users);
    }

    /**
     * @throws UserNotFoundException
     */
    public function findById(Request $request, Response $response): Response
    {
        $userId = (int)$request->getAttribute('id');
        $user = $this->userRepository->findUserById($userId);

        $this->logger->info("User of id `{$userId}` was viewed.");

        return $this->respondWithData($response, $user);
    }
}
