<?php

declare(strict_types=1);

use App\Infrastructure\Slim\Middleware\BasePathMiddleware;
use App\Infrastructure\Slim\Middleware\NoCacheMiddleware;
use App\Infrastructure\Slim\Middleware\RequestLoggingMiddleware;
use App\Infrastructure\Slim\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Interfaces\ErrorHandlerInterface;

/*
 * This middleware will be called in reverse order, so:
 *      - 1st added BodyParsingMiddleware will be the last middleware called
 *      - RequestLoggingMiddleware is the last middleware added, but the first to be called.
 */
return function (App $app) {
    // Add Body Parsing Middleware
    $app->addBodyParsingMiddleware();

    // Add Routing Middleware
    $app->addRoutingMiddleware();

    $app->add(BasePathMiddleware::class);
    $app->add(SessionMiddleware::class);
    $app->add(NoCacheMiddleware::class);

    // Add Error Middleware
    $errorMiddleware = $app->addErrorMiddleware(DISPLAY_ERRORS, LOGGER_REGISTER_ERRORS, LOGGER_REGISTER_ERRORS_DETAILS);
    $errorMiddleware->setDefaultErrorHandler(ErrorHandlerInterface::class);

    // this must be first and the last middleware called
    $app->add(RequestLoggingMiddleware::class);
};
