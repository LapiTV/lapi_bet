<?php

$app->get('/', \Bet\App\Controller\HomeController::class . ':homeAction')
    ->setName('Home');