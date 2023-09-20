<?php

declare(strict_types=1);

namespace App\Infrastructure\Slim\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

class SessionMiddleware implements Middleware
{
    public function __construct(private LoggerInterface $logger)
    {
        $this->logger->debug(self::class . " :: __construct");
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $this->logger->debug(self::class . " :: process");

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            session_start();
            $request = $request->withAttribute('session', $_SESSION);
        }

        return $handler->handle($request);
    }
}
