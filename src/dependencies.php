<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};


// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig($c->get('settings')['renderer']['template_path'], [
        'cache' => $c->get('settings')['debug'] ? $c->get('settings')['renderer']['cache_path'] : false,
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
    $view->getEnvironment()->addGlobal('currentUrl',$c['request']->getUri()->getPath());
    $view->getEnvironment()->addGlobal('session',$_SESSION);

    return $view;
};

$container['database'] = function ($c) {
    $dsn = 'mysql:host=' . $c->get('settings')['database']['host'] . ';dbname=' . $c->get('settings')['database']['database'] . ';charset=utf8';
    $usr = $c->get('settings')['database']['username'];
    $pwd = $c->get('settings')['database']['password'];

    $pdo = new \Slim\PDO\Database($dsn, $usr, $pwd);

    \Bet\App\Service\Database::setInstance($pdo);

    return $pdo;
};