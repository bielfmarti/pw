<?php

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class HomeController
{
    private  $container;



    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showHomePage(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'home.twig',
            []
        );
    }



    public function showLanding(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'landing.twig',
            []
        );
    }

    

}
