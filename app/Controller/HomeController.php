<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/21/17
 * Time: 10:34 AM
 */

namespace Bet\App\Controller;


use Slim\Http\Request;
use Slim\Http\Response;

class HomeController extends BaseController
{
    public function homeAction(Request $request, Response $response)
    {
        $this->view->render($response, 'home.html.twig');
    }
}