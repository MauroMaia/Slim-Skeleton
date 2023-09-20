<?php

declare(strict_types=1);

namespace App\Infrastructure\Slim\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

readonly class RequestLoggingMiddleware implements Middleware
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
        $this->logger->info(self::class . " :: process - New request",[
            "method"=>$request->getMethod(),
            "uri"=>$request->getUri()->getPath(),
            "query"=>$request->getUri()->getQuery(),
            "authority"=>$request->getUri()->getAuthority(),
            "fragment"=>$request->getUri()->getFragment(),
            "userinfo"=>$request->getUri()->getUserInfo(),
            "headers"=>$request->getHeaders(),
            "protocol"=>$request->getProtocolVersion(),
            "body"=>$request->getParsedBody(),
        ]);

        $response = $handler->handle($request);

        $this->logger->info(self::class . " :: process - Response",[
            "method"=>$request->getMethod(),
            "uri"=>$request->getUri()->getPath(),
            "headers"=>$response->getHeaders(),
            "status"=>$response->getStatusCode()     ,
            "body"=>$response->getBody()->getContents(),
        ]);

        return $response;
    }
}
