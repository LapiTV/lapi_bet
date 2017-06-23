<?php
/**
 * Created by PhpStorm.
 * User: Francois
 * Date: 21/06/2017
 * Time: 19:17
 */

namespace Bet\App\Middleware;


use Bet\App\Manager;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

class AuthMiddleware
{
    /** @var  Container */
    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if (!Manager\User::isLogin()) {
            /** @var Router $router */
            $router = $this->container->get('router');
            return $response->withRedirect($router->pathFor('Login'));
        } else {
            $response = $next($request, $response);
            return $response;
        }
    }
}