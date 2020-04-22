<?php

use \SallePW\SlimApp\Controller\HomeController;

//$app->get('/', HomeController::class . ':showHomePage')->setName('home');
$app->get('/', HomeController::class . ':showLanding')->setName('home');
$app->get('/sign-in', \SallePW\SlimApp\Controller\SignInController::class . ':showSignIn')->setName('signin');
$app->get('/sign-up', \SallePW\SlimApp\Controller\SignUpController::class . ':showSignUp')->setName('signup');




