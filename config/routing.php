<?php

use \SallePW\SlimApp\Controller\HomeController;
use \SallePW\SlimApp\Controller\RegisterController;
use \SallePW\SlimApp\Controller\SignInController;
use \SallePW\SlimApp\Controller\VisitsController;
use \SallePW\SlimApp\Middleware\StartSessionMiddleware;

$app->add(StartSessionMiddleware::class);

$app->get('/', HomeController::class . ':showLanding')->setName('home');

$app->get('/sign-up', RegisterController::class . ':showSignUp')->setName('sign-up');
$app->post('/sign-up', RegisterController::class . ':registerMe')->setName('sign-up');
$app->get('/sign-in', SignInController::class . ':showSignIn')->setName('sign-in');

$app->get(
    '/visits',
    VisitsController::class . ":showVisits"
)->setName('visits');