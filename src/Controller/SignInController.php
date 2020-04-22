<?php

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class SignInController
{
    private  $container;



    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function showSignIn(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'signin.twig',
            []
        );
    }


}