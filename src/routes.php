<?php

declare(strict_types=1);

use App\Application\Admin\RoleManagementController;
use App\Application\Admin\UserManagementController;
use App\Application\Dashboard\GenericController;
use App\Application\User\UserController;
use App\Application\User\LoginController;
use App\Domain\Role\Permissions;
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
    $app->map(['GET', 'POST'],'/logout', [LoginController::class, 'doLogout'])
        ->setName('doLogout');

    $app->group('/login', function (RouteCollectorProxy $group)
    {
        $group->get('', [LoginController::class, 'viewLoginAuth'])
            ->setName('viewLoginAuth');

        $group->get('/recover', [LoginController::class, 'viewLoginRecover'])
            ->setName('viewLoginRecover');

        $group->get('/recover/{id}/{recoverPassword}', [LoginController::class, 'viewLoginReset'])
            ->setName('viewLoginReset');

        //$group->post('/signup', [LoginController::class, 'viewSignupPage'])
        //      ->setName('viewSignupPage');
    })->add(Guard::class);

    $app->group('/app', function (RouteCollectorProxy $group) {

        $group->get('', [GenericController::class, 'viewDashboard'])
            ->setName('home')
            ->setArgument('permission',Permissions::READ_ONLY->value );

        $group->group('/admin', function (RouteCollectorProxy $group)
        {
            /*
             *  USER
             */

            $group->group('/users', function (RouteCollectorProxy $group)
            {
                $group->get('/add', [UserManagementController::class, 'viewAddUserForm'])
                    ->setName('viewAddUserForm')
                    ->setArgument('permission',Permissions::USER_MANAGEMENT->value);

                $group->get('/list', [UserManagementController::class, 'viewUsersList'])
                    ->setName('viewUsersList')
                    ->setArgument('permission', Permissions::USER_MANAGEMENT->value );
            });

            $group->get('/user/{id}', [UserController::class, 'viewUserProfile'])
                ->setName('viewAnotherUserProfile')
                ->setArgument('permission',Permissions::USER_MANAGEMENT->value );
            /*
             *  ROLE
             */
            $group->group('/role', function (RouteCollectorProxy $group)
            {
                $group->get('/list', [RoleManagementController::class, 'viewRoleList'])
                    ->setName('viewUsersList')
                    ->setArgument('permission',Permissions::USER_MANAGEMENT->value );
            });
        });

        $group->get('/profile/{id}', [UserController::class, 'viewUserProfile'])
            ->setName('viewUserProfile')
            ->setArgument('permission',Permissions::READ_ONLY->value );

    })->add(Guard::class);

    /*
     * API
     */
    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->post('/login', [LoginController::class, 'doLoginValidate'])
            ->setName('doLoginValidate');
        $group->post('/login/recover', [LoginController::class, 'doLoginRecover'])
            ->setName('doLoginRecover');
        $group->post('/login/reset', [LoginController::class, 'doLoginReset'])
            ->setName('doLoginReset');

        $group->group('/users', function (RouteCollectorProxy $group) {
            $group->post('', [UserManagementController::class, 'addUser'])
                ->setArgument('permission', Permissions::ADMIN->value );
        });

        $group->delete('/admin/user/{id}', [UserManagementController::class, 'deleteUser'])
            ->setName('deleteUser')
            ->setArgument('permission', Permissions::ADMIN->value );

        $group->group('/roles', function (RouteCollectorProxy $group) {

            $group->get('/list', [RoleManagementController::class, 'apiRolesList'])
                ->setArgument('permission', Permissions::ADMIN->value );

            $group->put('/', [RoleManagementController::class, 'apiCreateRole'])
                ->setArgument('permission', Permissions::ADMIN->value );

            $group->post('/{id}', [RoleManagementController::class, 'apiUpdateRole'])
                ->setArgument('permission', Permissions::ADMIN->value );

            $group->delete('/{id}', [RoleManagementController::class, 'apiDeleteRole'])
                ->setArgument('permission', Permissions::ADMIN->value );
        });
    });
};
