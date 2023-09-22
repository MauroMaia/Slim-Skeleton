<?php

declare(strict_types=1);

use App\Application\Admin\AdminController;
use App\Application\User\UserController;
use App\Application\User\LoginController;
use App\Infrastructure\Slim\Middleware\JWTAuthenticationHandler;
use App\Infrastructure\Slim\Middleware\NoCacheMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteCollectorProxy;
use Twig\Environment;

return function (App $app) {

    /*
     * BASIC ROUTES
     */

    // CORS Pre-Flight OPTIONS Request Handler
    $app->options('/{routes:.*}', fn(Request $request, Response $response) => $response);

    $app->get('/404[/]', function (Request $request, Response $response, Environment $twig) {
        $response->getBody()->write($twig->render('error/404.twig'));
        return $response->withHeader('Content-Type', 'text/html');
    });

    $app->get('/500[/]', function (Request $request, Response $response, Environment $twig) {
        $response->getBody()->write($twig->render('error/500.twig'));
        return $response->withHeader('Content-Type', 'text/html');
    });

    $app->get('/', function (Request $request, Response $response, RouteParserInterface $router){
        return $response->withStatus(301)
            ->withHeader('Location', $router->urlFor('viewLoginAuth'));
    });

    $app->get('/app/terms', function (Request $request, Response $response, Environment $twig) {
        $response->getBody()->write($twig->render('terms/main.twig'));
        return $response->withHeader('Content-Type', 'text/html');
    })->setName('terms');

    /*
     * NO-AUTHENTICATION
     */
    $app->group('/login', function (RouteCollectorProxy $group)
    {
        $group->get('', [LoginController::class, 'viewLoginAuth'])
            ->setName('viewLoginAuth');

        $group->get('/recover', [LoginController::class, 'viewLoginRecover'])
            ->setName('viewLoginRecover');

        $group->get('/recover/{id}/{recoverPassword}', [LoginController::class, 'viewLoginReset'])
            ->setName('viewLoginReset');

        //$group->post('/signup', [LoginController::class, 'getSignupPage'])
        //      ->setName('doLoginReset');
    })->add(Guard::class);

    $app->group('/logout', function (RouteCollectorProxy $group) {
        $group->get('', [LoginController::class, 'doLogout'])->setName('doLogout');
        $group->post('', [LoginController::class, 'doLogout'])->setName('doLogout');
    })->add(NoCacheMiddleware::class)
        ->add(JWTAuthenticationHandler::class);

    $app->group('/app', function (RouteCollectorProxy $group) {

        $group->get('', function (Request $request, Response $response, Environment $twig) {
            $response->getBody()->write($twig->render('dashboard.twig'));
            return $response->withHeader('Content-Type', 'text/html');
        })->setName('home');

        $group->group('/admin', function (RouteCollectorProxy $group)
        {
            $group->group('/users', function (RouteCollectorProxy $group)
            {
                $group->get('/add', [AdminController::class, 'viewAddUserForm'])
                      ->setName('viewAddUserForm');

                $group->get('/list', [AdminController::class, 'viewUsersList'])
                      ->setName('viewUsersList');

            });

            $group->get('/user/{id}', [UserController::class, 'viewUserProfile'])
                ->setName('viewUserProfile');

        });

        $group->get('/profile/{id}', [UserController::class, 'viewUserProfile'])
              ->setName('viewUserProfile');

    })->add(Guard::class)
      ->add(JWTAuthenticationHandler::class);

    /*
     * API - NO-AUTHENTICATION
     */
    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->post('/login', [LoginController::class, 'doLoginValidate'])->setName('doLoginValidate');
        $group->post('/login/recover', [LoginController::class, 'doLoginRecover'])->setName('doLoginRecover');
        $group->post('/login/reset', [LoginController::class, 'doLoginReset'])->setName('doLoginReset');
    })->add(NoCacheMiddleware::class);

    /*
     * API - REQUIRES AUTHENTICATION
     */
    $app->group('/secure/api', function (RouteCollectorProxy $group) {

        $group->group('/users', function (RouteCollectorProxy $group) {
            $group->post('', [AdminController::class, 'addUser']);
        });

        $group->delete('/admin/user/{id}', [UserController::class, 'deleteUserProfile'])->setName('deleteUserProfile');

    })->add(JWTAuthenticationHandler::class)
        ->add(NoCacheMiddleware::class);
};
