<?php
/**
 * Created by PhpStorm.
 * User: isak
 * Date: 6/21/17
 * Time: 10:43 AM
 */

namespace Bet\App\Controller;


use Monolog\Logger;
use Slim\Container;
use Slim\PDO\Database;
use Slim\Views\Twig;

class BaseController
{
    /** @var Database */
    protected $database;

    /** @var Twig */
    protected $view;

    /** @var Container */
    protected $container;

    /** @var Logger */
    protected $logger;

    public function __construct(Container $container)
    {
        $this->view = $container->view;

        $this->database = $container->database;

        $this->logger = $container->logger;

        $this->container = $container;
    }
}