<?php

namespace App\Application\Admin;

use App\Domain\Role\RoleRepository;
use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\Slim\HttpResponse;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Message;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserManagementController
{
    use HttpResponse;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserRepository  $userRepository,
        private readonly RoleRepository  $roleRepository
    ) { }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function viewAddUserForm(Request $request, Response $response, Environment $twig): Response|Message
    {
        $response->getBody()->write($twig->render('pages/admin/add-user.twig', [
            "roles"=> $this->roleRepository->findAll(),
        ]));
        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function viewUsersList(Request $request, Response $response, Environment $twig): Response|Message
    {
        $userList = $this->userRepository->findAll();

        $response->getBody()->write($twig->render('pages/admin/list-users.twig',
            [
                "userList" => $userList
            ])
        );
        return $response->withHeader('Content-Type', 'text/html');
    }


    public function addUser(Request              $request,
                            Response             $response,
                            RouteParserInterface $router): Response|Message
    {
        $firstName = $request->getParsedBody()['firstName'];
        $lastName = $request->getParsedBody()['lastName'];
        $password = $request->getParsedBody()['password'];

        $user = new User(
            id:              -1,
            username:        $firstName . '.' . $lastName,
            firstName:       $firstName,
            lastName:        $lastName,
            password:        password_hash($password, null),
            recoverPassword: '',
            email:           $request->getParsedBody()['email'],
            jobTitle:        '',
            roleId:          $request->getParsedBody()['role']
        );

        // check if user already exist in the database
        try {
            // FIXME - join this two validation in one query
            $this->userRepository->findByEmail($user->email);
            $this->userRepository->findByUsername($user->getUsername());

            throw new InvalidArgumentException("User Already exist");
        } catch (UserNotFoundException) { }

        $user = $this->userRepository->add($user);

        $this->logger->info("New user added", ["user" => $user]);

        /*EmailHandler::SendWelcomeEmail(
            $user,
            $router->fullUrlFor($request->getUri(),'home',['id'=>$user->id,'recoverPassword'=>$recoverPassword]),
            $twig
        );*/

        return $response->withStatus(301)->withHeader('Location', $router->urlFor('viewUsersList'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response|Message
     */
    public function deleteUser(Request $request, Response $response): Response|Message
    {
        $userId = (int)$request->getAttribute('id');
        if($this->userRepository->delete($userId)){
            return $response->withStatus(200);
        }
        return $response->withStatus(400);
    }
}

