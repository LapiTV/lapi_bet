<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 8/13/17
 * Time: 5:08 PM
 */

namespace Bet\App\Controller;


use Slim\Http\Request;
use Slim\Http\Response;
use Slim\PDO\Database;

class UserController extends BaseController
{
    public function ajaxGetMessageWinner(Request $request, Response $response)
    {
        $pdo = $this->getDatabaseMessage();

        $username = $request->getParam('winner');
        $date = $request->getParam('date');
        $date = (new \DateTime())->setTimestamp($date);

        if (empty($username) || empty($date)) {
            return $response->withJson(['messages' => []]);
        }

        $messages = $pdo->select()->from('message')
            ->where('username', '=', $username)
            ->where('sent', '>', $date->format('Y-m-d H:i:s'))
            ->orderBy('sent')
            ->execute()->fetchAll();

        return $response->withJson(['messages' => $messages]);
    }

    private function getDatabaseMessage()
    {
        $dsn = $this->container->get('settings')['databaseMessage']['driver'] . ':host=' . $this->container->get('settings')['databaseMessage']['host'] . ';dbname=' . $this->container->get('settings')['databaseMessage']['database'];
        $usr = $this->container->get('settings')['databaseMessage']['username'];
        $pwd = $this->container->get('settings')['databaseMessage']['password'];

        return new Database($dsn, $usr, $pwd);
    }
}