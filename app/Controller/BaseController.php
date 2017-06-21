<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/21/17
 * Time: 10:43 AM
 */

namespace Bet\App\Controller;


use Slim\Container;
use Slim\Views\Twig;

class BaseController
{
    /** @var Twig */
    protected $view;

    public function __construct(Container $container)
    {
        $this->view = $container->view;
    }
}