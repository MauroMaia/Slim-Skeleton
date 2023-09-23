<?php
declare(strict_types=1);

$vendorDir = __DIR__ . '/vendor';
if (is_dir($vendorDir) === false)
{
    echo "The 'vendor' folder does not exist. Please run composer to install dependencies.";
    return;
}
use App\Infrastructure\Slim\Handlers\ShutdownHandler;
use DI\Bridge\Slim\Bridge;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Interfaces\ErrorHandlerInterface;

require __DIR__ . '/vendor/autoload.php';

// Start PHP session
session_start();

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = require __DIR__ . '/src/dependencies.php';

if (PRODUCTION)
{
	$containerBuilder->enableCompilation(__DIR__ . '/var/cache');
}

// Set up repositories
$repositories = require __DIR__ . '/src/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
$app = Bridge::create($container);

// Register middleware
$middleware = require __DIR__ . '/src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/src/routes.php';
$routes($app);

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $container->get(ErrorHandlerInterface::class), DISPLAY_ERRORS);
register_shutdown_function($shutdownHandler);


// Run App
$app->run();
