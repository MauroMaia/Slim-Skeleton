<?php

namespace App\Infrastructure\Slim\Middleware;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Infrastructure\Slim\Authentication\Token;
use Firebase\JWT\SignatureInvalidException;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Twig\Environment;

readonly class JWTAuthenticationHandler
{

    public function __construct(private LoggerInterface      $logger,
                                private RouteParserInterface $router,
                                private UserRepository       $userRepository,
                                private Environment          $twig
    )
    {
        $this->logger->debug(self::class . " :: __construct");
    }

    /**
     *
     * @param Request         $request
     * @param RequestHandler  $handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->logger->debug(self::class . " :: __invoke");

        $token = $request->getCookieParams()['token'] ?? null;

        if ($token)
        {
            try {
                $token = new Token(token: $token);
                $token->decode();

                $user = $this->userRepository->findById((int)$token->subject);

                $request = $request->withAttribute('user', $user);

                $this->twig->addGlobal('currentUserFirstName', $user->getFirstName());
                $this->twig->addGlobal('currentUserLastName', $user->getLastName());
                $this->twig->addGlobal('currentUserid', $user->id);
            } catch (SignatureInvalidException|\TypeError|UserNotFoundException $ignored) {
                $this->logger->warning("Invalid user authentication", ['exception-message' => $ignored->getMessage()]);
                $response = new Response();
                return $response->withStatus(301)
                    ->withHeader('Location', $this->router->urlFor('viewLoginAuth'));
            }
        }

        return $handler->handle($request);
    }
}


