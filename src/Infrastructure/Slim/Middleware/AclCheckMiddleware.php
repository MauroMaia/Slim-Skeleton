<?php

namespace App\Infrastructure\Slim\Middleware;

use App\Domain\Role\Permissions;
use App\Domain\User\UserRepository;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

readonly class AclCheckMiddleware
{

    public function __construct(private LoggerInterface      $logger,
                                private UserRepository       $userRepository,
                                private RouteParserInterface $router
    )
    {
        $this->logger->notice(self::class . " :: __construct");
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

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $requiredPermission = $route->getArgument('permission', Permissions::GUEST->value);

        $user = $request->getAttribute('user');

        if($user !== null) {
            $userPermissions = $this->userRepository->getUserPermissions($user->id);

            if ($this->isAllowed($userPermissions, $requiredPermission) === false) {
                $this->logger->warning("User does not have privileges to access this resource",
                    [
                        "userPermissions"       =>  $userPermissions,
                        "requiredPermission"    =>  $requiredPermission,
                        "url"                   =>  $request->getUri()->getPath()
                    ]
                );
                $response = new Response();
                return $response->withStatus(403);
            }
        } elseif ($this->isAllowed(array(Permissions::GUEST->value => 1),$requiredPermission) === false) {
            $this->logger->warning("Guest does not have privileges to access this resource",
                [
                    "userPermissions"       =>  [Permissions::GUEST->value],
                    "requiredPermission"    =>  $requiredPermission,
                    "url"                   =>  $request->getUri()->getPath()
                ]
            );
            $response = new Response();
            return $response->withStatus(301)
                ->withHeader('Location', $this->router->urlFor('viewLoginAuth'));
        }

        return $handler->handle($request);
    }

    private function isAllowed(array $userPermissions, string $permission): bool
    {
        return (
                array_key_exists(Permissions::ADMIN->value, $userPermissions) &&
                $userPermissions[Permissions::ADMIN->value] == 1
            )
            or
            (
                array_key_exists($permission, $userPermissions) &&
                $userPermissions[$permission] == 1
            );
    }
}


