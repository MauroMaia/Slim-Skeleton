<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\DatabaseConnection;
use App\Infrastructure\Slim\CsrfExtension;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Interfaces\RouteParserInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(
    [
        LoggerInterface::class => function () {

            $loggerSettings = LOGGER_INTERNAL_CONFIGS;
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        // Configure Twig
        Environment::class => function (Guard $guard) {
            $loader = new FilesystemLoader(__DIR__ . '/../src/View');
            $twig = new Environment($loader);
            //$twig->addGlobal('project_owner_url', PROJECT_OWNER_URL);
            //$twig->addGlobal('project_owner_name', PROJECT_OWNER_NAME);
            $twig->addGlobal('base_path', BASE_PATH);
            $twig->addGlobal('app_name', APP_NAME);
            $twig->addGlobal('app_description', APP_DESCRIPTION);

            $twig->addExtension(new CsrfExtension($guard));

            return $twig;
        },

        DatabaseConnection::class => fn(LoggerInterface $logger) => new DatabaseConnection($logger),

        Guard::class => function (App $app, LoggerInterface $logger)
        {
            $guard = new Guard($app->getResponseFactory(), persistentTokenMode: true);
            $guard->setFailureHandler(function () use ($logger, $app)
            {
                $logger->warning("Invalid csrf token");
                return $app->getResponseFactory()->createResponse(400);
            });

            return $guard;
        },

        RouteParserInterface::class => fn(App $app) => $app->getRouteCollector()->getRouteParser()
    ]);

return $containerBuilder;
