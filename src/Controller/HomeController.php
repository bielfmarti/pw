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



    public function showLanding(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'landing.twig',
            [
                'is_login' => isset($_SESSION['is_login']),

            ]
        );
    }

    public function showSignIn(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'signin.twig',
            []
        );
    }

    public function signout(Request $request, Response $response): Response{


        session_unset();

        unset($_SESSION['is_login']);
        unset($_SESSION['login']);


        return $response->withStatus(302)->withHeader('Location', '/');
    }


    public function provaProfile(Request $request, Response $response): Response{


        return $this->container->get('view')->render(
            $response,
            'profile.twig',
            []
        );    }



}
