<?php

namespace App\Application\User;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\EmailHandler;
use App\Infrastructure\Slim\Authentication\Token;
use App\Infrastructure\Slim\HttpResponse;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouteParserInterface;
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

        if(empty($user->recoverPassword) || !password_verify($recoverPassword,$user->recoverPassword)) {
            $response->getBody()->write($twig->render('error/flow-end.twig', [
                'title'=>'Invalid URL',
                'subtitle'=>'This link it\'s no longer valid',
                'lead'=>'Please try again.'
            ]));
            return $response->withHeader('Content-Type', 'text/html');
        }

        // Set a new recover password to prevent replay attack
        $recoverPassword = rand_string(32);
        $passwordHash = password_hash($recoverPassword, null);
        $this->userRepository->updateUserRecoverPassword($user, $passwordHash);

        $response->getBody()->write($twig->render('reset-password.twig', [
            'userId' => $user->id,
            'recoverHash' => $recoverPassword
        ]));
        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Environment $twig
     * @param RouteParserInterface $router
     *
     * @return Response|Message
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function doLoginRecover(Request $request, Response $response, Environment $twig, RouteParserInterface $router): Response|Message
    {
        $email = $request->getParsedBody()['email'];
        $user = $this->userRepository->findUserByEmail($email);

        $recoverPassword = rand_string(32);
        $passwordHash = password_hash($recoverPassword, null);

        $this->userRepository->updateUserRecoverPassword($user, $passwordHash);
        $this->logger->info("Use this password in url: /recover/" . $user->id . "/" . $recoverPassword);

        EmailHandler::SendRecoverEmail(
            $user,
            $router->fullUrlFor($request->getUri(),'viewLoginReset',['id'=>$user->id,'recoverPassword'=>$recoverPassword]),
            $twig
        );

        $response->getBody()->write($twig->render('error/flow-end.twig', [
            'title'=>'Email Sent',
            'subtitle'=>'A recover email was sent to your email account',
            'lead'=>'In case off any issue contact the support.'
        ]));
        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * @throws UserNotFoundException
     */
    public function doLoginReset(Request $request, Response $response, RouteParserInterface $router): Response|Message
    {
        $password = $request->getParsedBody()['password'];
        $userId = $request->getParsedBody()['code'];
        $recoverPassword = $request->getParsedBody()['recoverHash'];

        $user = $this->userRepository->findUserById($userId);

        if (password_verify($recoverPassword, $user->recoverPassword)) {

            $passwordHash = password_hash($password, null);

            $this->userRepository->updateUserPassword($user, $passwordHash);

            return $response->withStatus(301)->withHeader('Location', $router->urlFor('viewLoginAuth'));
        } else {
            return $response->withStatus(403);
        }
    }

    /**
     * @throws UserNotFoundException
     */
    public function doLoginValidate(Request $request, Response $response, RouteParserInterface $router)
    {
        $username = $request->getParsedBody()['username'];
        $password = $request->getParsedBody()['password'];

        $user = $this->userRepository->findUserByUsername($username);
        if (password_verify($password, $user->password)) {
            $token = new Token($user->getUsername());
            $token->encode();
            setcookie("token", $token->token, time() + 3600, BASE_PATH);
            return $response->withStatus(301)->withHeader('Location', $router->urlFor('dashboard'));
        } else {
            return $response->withStatus(403);
        }
    }

    public function doLogout(Request $request, Response $response, RouteParserInterface $router): Response|Message
    {
        setcookie("token", "", 0, "");
        return $response->withStatus(301)->withHeader('Location', $router->urlFor('viewLoginAuth'));
    }
}
