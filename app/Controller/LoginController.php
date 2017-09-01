<?php
/**
 * Created by PhpStorm.
 * User: Francois
 * Date: 21/06/2017
 * Time: 19:25
 */

namespace Bet\App\Controller;


use Bet\App\Exception\FormException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

class LoginController extends BaseController
{
    public function loginAction(Request $request, Response $response)
    {
        if ($request->getMethod() === 'POST') {
            $data = [
                'username' => $request->getParam('username'),
                'password' => $request->getParam('password')
            ];

            try {
                $this->tryLogin($data['username'], $data['password']);

                /** @var Router $router */
                $router = $this->container->get('router');
                return $response->withRedirect($router->pathFor('Create_Bet'));
            } catch (FormException $fe) {
                $error = $fe->getMessage();
            } catch (\Exception $e) {
                $this->logger->warning("Login", [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'trace' => $e->getTrace(),
                ]);
                $error = 'Une erreur est survenue lors de la connexion.';
            }
        }

        $this->view->render($response, 'login.html.twig', [
            'error' => $error ?? '',
            'data' => $data ?? [],
        ]);
    }

    private function tryLogin(string $username, string $password)
    {
        $user = $this->database->select()->from('"user"')->where('username', '=', $username);
        $user = $user->execute()->fetch();

        if (empty($user) || !password_verify($password, $user['password'])) {
            throw new FormException('L\'utilisateur ou le mot de passe n\'est pas valide.');
        }

        $this->setSession($user);
    }

    private function setSession($user)
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
        ];
    }

    public function logoutAction(Request $request, Response $response)
    {
        unset($_SESSION['user']);

        /** @var Router $router */
        $router = $this->container->get('router');
        return $response->withRedirect($router->pathFor('Home'));
    }
}