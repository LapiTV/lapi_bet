<?php

$container = $app->getContainer();

$app->get('/', \Bet\App\Controller\HomeController::class . ':homeAction')
    ->setName('Home');

$app->map(['GET', 'POST'], '/login', \Bet\App\Controller\LoginController::class . ':loginAction')
    ->setName('Login');

$app->get('/logout', \Bet\App\Controller\LoginController::class . ':logoutAction')
    ->setName('Logout')
    ->add(new Bet\App\Middleware\AuthMiddleware($container));

$app->group('/bet', function () use ($container) {
    $this->get('', \Bet\App\Controller\Bet\BetController::class . ':listBetAction')
        ->setName('List_Bet');

    $this->map(['GET', 'POST'], '/create', \Bet\App\Controller\Bet\BetController::class . ':createBet')
        ->setName('Create_Bet');

    $this->get('/{betId:[0-9]+}', \Bet\App\Controller\Bet\BetController::class . ':displayBetAction')
        ->setName('Display_Bet');
})
    ->add(new Bet\App\Middleware\AuthMiddleware($container));

$app->get('/ajax/bet/{betId:[0-9]+}', \Bet\App\Controller\Bet\BetController::class . ':ajaxGetDataBet')
    ->setName('Ajax_Get_Data_Bet');

$app->post('/ajax/bet/{betId:[0-9]+}/winner', \Bet\App\Controller\Bet\BetController::class . ':ajaxGetWinnerBet')
    ->setName('Ajax_Get_Winner_Bet')
    ->add(new Bet\App\Middleware\AuthMiddleware($container));