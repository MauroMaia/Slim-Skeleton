<?php

namespace App\Application\User;

use App\Application\HttpResponse;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\EmailHandler;
use App\Infrastructure\Slim\Authentication\Token;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Message;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
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
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws UserNotFoundException
     */
    public function viewLoginReset(Request $request, Response $response, Environment $twig): Response|Message
    {
        $userId = (int)$request->getAttribute('id');
        $recoverPassword = $request->getAttribute('recoverPassword');
        $user = $this->userRepository->findUserById($userId);

        if (password_verify($recoverPassword, $user->recoverPassword)) {
            $response->getBody()->write($twig->render('reset-password.twig', []));
            return $response->withHeader('Content-Type', 'text/html');
        } else {
            return $response->withStatus(301)->withHeader('Location', BASE_PATH . '/login');
        }
    }


    /**
     * @throws UserNotFoundException
     */
    public function doLoginValidate(Request $request, Response $response)
    {
        $username = $request->getParsedBody()['username'];
        $password = $request->getParsedBody()['password'];


        $user = $this->userRepository->findUserByUsername($username);
        if (password_verify($password, $user->password)) {
            $token = new Token($user->getUsername());
            $token->encode();
            setcookie("token", $token->token, time() + 3600, BASE_PATH);
            return $response->withStatus(301)->withHeader('Location', BASE_PATH . '/dashboard');
        } else {
            return $response->withStatus(403);
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function doLoginRecover(Request $request, Response $response, Environment $twig): Response|Message
    {
        $email = $request->getParsedBody()['email'];
        $user = $this->userRepository->findUserByEmail($email);

        $recoverPassword = rand_string(32);
        $passwordHash = password_hash($recoverPassword, null);

        $this->userRepository->updateUserRecoverPassword($user, $passwordHash);
        $this->logger->info("Use this password in url: /recover/" . $user->id . "/" . $recoverPassword);

        EmailHandler::SendRecoverEmail(
            $user,
            $request->getUri()->getScheme() . '://' . $request->getUri()->getHost()
            . BASE_PATH . "/login/recover/" . $user->id . "/" . $recoverPassword,
            $twig
        );

        // TODO - replace this with proper/success page loading
        return $response->withStatus(301)->withHeader('Location', BASE_PATH . '/login');
    }

    public function doLogout(Request $request, Response $response): Response|Message
    {
        setcookie("token", "", 0, "");
        return $response->withStatus(301)->withHeader('Location', BASE_PATH . '/login');
    }
}
