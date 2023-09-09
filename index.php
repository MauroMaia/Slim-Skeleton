<?php

declare(strict_types=1);

use App\Infrastructure\Slim\Handlers\HttpErrorHandler;
use App\Infrastructure\Slim\Handlers\ShutdownHandler;
use DI\Bridge\Slim\Bridge;
use Psr\Log\LoggerInterface;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/vendor/autoload.php';

// Start PHP session
session_start();

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = require __DIR__ . '/src/dependencies.php';

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/var/cache');
}

// Set up repositories
$repositories = require __DIR__ . '/src/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
$app = Bridge::create($container);
$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require __DIR__ . '/src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/src/routes.php';
$routes($app);

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory,$app->getContainer()->get(LoggerInterface::class));

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, DISPLAY_ERRORS);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();
$app->setBasePath(BASE_PATH);

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(DISPLAY_ERRORS, LOGGER_REGISTER_ERRORS, LOGGER_REGISTER_ERRORS_DETAILS);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App
$app->run();
