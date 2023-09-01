<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

$containerBuilder = new ContainerBuilder();


$containerBuilder->addDefinitions(
    [
        SettingsInterface::class => function ()
        {
            return new Settings(
                [
                    'displayErrorDetails' => true, // Should be set to false in production
                    'logError'            => false,
                    'logErrorDetails'     => false,
                    'logger'              => [
                        'name'  => 'slim-app',
                        'path'  => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                        'level' => Level::Debug,
                    ],
                ]);
        },
        LoggerInterface::class   => function (ContainerInterface $c)
        {
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
    ]);

return $containerBuilder;
