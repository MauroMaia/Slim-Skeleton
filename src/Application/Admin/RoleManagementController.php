<?php

namespace App\Application\Admin;


use App\Domain\Role\Permissions;
use App\Domain\Role\Role;
use App\Domain\Role\RoleRepository;
use App\Infrastructure\Slim\HttpResponse;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Message;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RoleManagementController
{
    use HttpResponse;

    public function __construct(public LoggerInterface $logger, public RoleRepository $roleRepository) { }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function viewRoleList(Request $request, Response $response, Environment $twig): Response|Message
    {

        $response->getBody()->write($twig->render('pages/admin/list-roles.twig', [
            "permissions"=>array_column(Permissions::cases(), 'value')
        ]));
        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response|Message
     */
    public function apiRolesList(Request $request, Response $response): Response|Message
    {
        $roles = $this->roleRepository->findAll();
        $permissions= array_column(Permissions::cases(), 'value');
        asort($permissions,SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
        $permissions=array_values($permissions);

        $response->getBody()->write(json_encode((object)[
            "permissions"=>$permissions,
            "roles" => $roles
        ]));

        return $response->withHeader('Content-Type', 'text/html');
    }

    public function apiCreateRole(Request $request, Response $response): Response|Message
    {
        $body = $request->getParsedBody();

        $id = $this->roleRepository->create(new Role(
            null,
            $body['name'],
            array_filter(
                array_column(Permissions::cases(), 'value'),
                fn( $element) => filter_var($body[$element],FILTER_VALIDATE_BOOLEAN) == true
            )
        ));

        if(!$id){
            return $response->withStatus(400);
        }

        return $response->withStatus(204);
    }

    public function apiUpdateRole(Request $request, Response $response): Response|Message
    {
        // find if role id still exist
        $id = (int)$request->getAttribute('id');
        $role = $this->roleRepository->find($id);

        if ($role === false)
        {
            return $response->withStatus(404);
        }

        // parse and save body
        $body = $request->getParsedBody();

        $role = new Role(
            $role->id,
            $body['name'],
            // todo this is not working
            array_filter(
                array_column(Permissions::cases(), 'value'),
                fn( $element) => filter_var($body[$element],FILTER_VALIDATE_BOOLEAN) == true
            ),
            null, null
        );

        if($this->roleRepository->update($role) === false){
            return $response->withStatus(400);
        }

        $role = $this->roleRepository->find($id);
        $response->getBody()->write(json_encode($role));
        return $response->withStatus(200);
    }

    public function apiDeleteRole(Request $request, Response $response): Response|Message
    {
        $userId = (int)$request->getAttribute('id');
        if($this->roleRepository->delete($userId)){
            return $response->withStatus(200);
        }
        return $response->withStatus(400);
    }
}

