<?php

declare(strict_types=1);

use App\Infrastructure\Settings\Settings;
use App\Infrastructure\Settings\SettingsInterface;
use App\Infrastructure\Persistence\DatabaseConnection;
use App\Infrastructure\Slim\CsrfExtension;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(
    [
        SettingsInterface::class => function () {
            return new Settings(
                [
                    'displayErrorDetails' => true, // Should be set to false in production
                    'logError' => false,
                    'logErrorDetails' => false,
                    'logger' => [
                        'name' => 'slim-app',
                        'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                        'level' => Level::Debug,
                    ],
                ]);
        },
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'],
                $loggerSettings['level']);
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
            $twig->addGlobal('app_name', BASE_PATH);
            //$twig->addGlobal('app_description', APP_DESCRIPTION);

            $twig->addExtension(new CsrfExtension($guard));

            return $twig;
        },

        DatabaseConnection::class => fn(LoggerInterface $logger) => new DatabaseConnection($logger),

        Guard::class => fn(App $app) => new Guard($app->getResponseFactory(), persistentTokenMode: true),
    ]);

return $containerBuilder;
