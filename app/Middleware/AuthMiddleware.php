<?php
/**
 * Created by PhpStorm.
 * User: Francois
 * Date: 21/06/2017
 * Time: 19:17
 */

namespace Bet\App\Middleware;


use Slim\Container;
use Slim\Router;

class AuthMiddleware
{
    /** @var  Container */
    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next)
    {
        if (empty($_SESSION) || empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            /** @var Router $router */
            $router = $this->container->get('router');
            return $response->withRedirect($router->pathFor('Login'));
        } else {
            $response = $next($request, $response);
            return $response;
        }
    }
}