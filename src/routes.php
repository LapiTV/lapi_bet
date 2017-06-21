<?php

$app->get('/', \Bet\App\Controller\HomeController::class . ':homeAction')
    ->setName('Home');

$app->map(['GET', 'POST'], '/createBet', \Bet\App\Controller\Bet\BetController::class . ':createBet')
    ->setName('Create_Bet');