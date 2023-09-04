<?php

namespace App\Application\Actions\User;

use App\Application\Actions\HttpResponse;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\Slim\Authentication\Token;
use Slim\Psr7\Message;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class LoginController
{
    use HttpResponse;
    public function __construct(public LoggerInterface $logger, public UserRepository $userRepository) { }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function viewLoginAuth(Request $request, Response $response, Environment $twig): Response|Message
    {
        $response->getBody()->write($twig->render('login.twig', []));
        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function viewLoginRecover(Request $request, Response $response, Environment $twig): Response|Message
    {
        $response->getBody()->write($twig->render('recover.twig', []));
        return $response->withHeader('Content-Type', 'text/html');
    }
    /**
     * @throws UserNotFoundException
     */
    public function doLoginValidate(Request $request, Response $response)
    {
        $username = $request->getParsedBody()['username'];
        $password = $request->getParsedBody()['password'];


        $user = $this->userRepository->findUserByUsername($username);
        if(password_verify($password, $user->password )){
            $token = new Token($user->getUsername());
            $token->encode();
            setcookie("token", $token->token, time()+3600, BASE_PATH);
            return $response->withStatus(301)->withHeader('Location', BASE_PATH . '/dashboard');
        }else{
            return $response->withStatus(403);
        }
    }

    public function doLogout(Request $request, Response $response): Response|Message
    {
        setcookie("token", "", 0, "");
        return $response->withStatus(301)->withHeader('Location', BASE_PATH.'/login/auth');
    }
}
