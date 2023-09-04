<?php

declare(strict_types=1);

use App\Infrastructure\Slim\Middleware\NoCacheMiddleware;
use App\Infrastructure\Slim\Middleware\SessionMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->add(NoCacheMiddleware::class);
};
