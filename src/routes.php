<?php

$container = $app->getContainer();

$app->get('/', \Bet\App\Controller\HomeController::class . ':homeAction')
    ->setName('Home');

$app->map(['GET', 'POST'], '/login', \Bet\App\Controller\LoginController::class . ':loginAction')
    ->setName('Login');

$app->get('/logout', \Bet\App\Controller\LoginController::class . ':logoutAction')
    ->setName('Logout')
    ->add(new Bet\App\Middleware\AuthMiddleware($container));

$app->map(['GET', 'POST'], '/createBet', \Bet\App\Controller\Bet\BetController::class . ':createBet')
    ->setName('Create_Bet')
    ->add(new Bet\App\Middleware\AuthMiddleware($container));

$app->get('/listBet', \Bet\App\Controller\Bet\BetController::class . ':listBetAction')
    ->setName('List_Bet')
    ->add(new Bet\App\Middleware\AuthMiddleware($container));