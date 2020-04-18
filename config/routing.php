<?php

use \SallePW\SlimApp\Controller\HomeController;

$app->get('/', HomeController::class . ':showHomePage')->setName('home');






/*
 * Amb controller, aquest hauria de ser el codi, quan funcioni la classe HomeController, posa aquest codi
 *
<?php

use \SallePW\SlimApp\Controller\HomeController;

$app->get('/', HomeController::class . ':showHomePage')->setName('home');
 */




/*
<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app->get(
    '/',
    function (Request $request, Response $response) {
        return $this->get('view')->render($response, 'home.twig', []);
    }
)->setName('home');

 *
 */