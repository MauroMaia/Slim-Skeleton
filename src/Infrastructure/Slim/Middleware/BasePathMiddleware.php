<?php


namespace App\Infrastructure\Slim\Middleware;

use App\Infrastructure\Slim\BasePathDetector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Slim 4 Base path middleware.
 */
final class BasePathMiddleware
{
    /**
     * @var App The slim app
     */
    private $app;

    /**
     * @var string|null
     */
    private $phpSapi;

    /**
     * The constructor.
     *
     * @param App $app The slim app
     * @param string|null $phpSapi The PHP_SAPI value
     */
    public function __construct(App $app, string $phpSapi = null)
    {
        $this->app = $app;
        $this->phpSapi = $phpSapi;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $detector = new BasePathDetector($request->getServerParams(), $this->phpSapi);

        $this->app->setBasePath($detector->getBasePath());
        return $handler->handle($request);
    }
}
