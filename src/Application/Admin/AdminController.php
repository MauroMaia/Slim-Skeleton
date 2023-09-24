<?php

namespace App\Application\Admin;

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

class AdminController
{
    use HttpResponse;

    public function __construct(public LoggerInterface $logger, public UserRepository $userRepository) { }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function viewAddUserForm(Request $request, Response $response, Environment $twig): Response|Message
    {
        $response->getBody()->write($twig->render('pages/admin/add-user.twig', []));
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

        $response->getBody()->write($twig->render('pages/admin/list-users.twig', ["userList" => $userList]));
        return $response->withHeader('Content-Type', 'text/html');
    }


    public function addUser(Request              $request,
                            Response             $response,
                            Environment          $twig,
                            RouteParserInterface $router): Response|Message
    {
        $firstName = $request->getParsedBody()['firstName'];
        $lastName = $request->getParsedBody()['lastName'];
        $email = $request->getParsedBody()['email'];
        $password = $request->getParsedBody()['password'];

        try {
            $this->userRepository->findByEmail($email);
            throw new InvalidArgumentException("User Already exist");
        } catch (UserNotFoundException $ignore) {
        }


        $passwordHash = password_hash($password, null);

        $user = $this->userRepository->add(
            new User(
                id:              -1,
                username:        $firstName . '.' . $lastName,
                firstName:       $firstName,
                lastName:        $lastName,
                password:        $passwordHash,
                recoverPassword: '',
                email:           $email,
                jobTitle:        '',
                createdAt:       null,
                updatedAt:       null
            )
        );
        //$this->logger->info("New user added", ["id" => $user->id]);
        $this->logger->info("New user added", []);

        /*EmailHandler::SendWelcomeEmail(
            $user,
            $router->fullUrlFor($request->getUri(),'home',['id'=>$user->id,'recoverPassword'=>$recoverPassword]),
            $twig
        );*/


        return $response->withStatus(301)->withHeader('Location', $router->urlFor('viewUsersList'));
    }

}

