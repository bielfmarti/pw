<?php

namespace SallePW\SlimApp\Controller;

use PDO;
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
            [
              'status' => "Please Sign In",
                'is_login' => isset($_SESSION['is_login']),
                ]
        );
    }

    public function login(Request $request, Response $response): Response
    {

        $success = "Please Sign In";
        $error = "";
        $valid = true;


        if (empty($_POST)) {

            exit;
        }



        if (empty($_POST['email']) || empty($_POST['password'])) {

            $valid = false;
        }

        $email = $_POST['email'];

        $password = $_POST['password'];



        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $valid = false;
        }else{


            $email = isset($_POST['email']) ? trim($_POST['email']) : null;

            // List of allowed domains
            $allowed = [
                'salle.url.edu'
            ];

            // Make sure the address is valid
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) //validem email
            {
                // Separate string by @ characters (there should be only one)
                $parts = explode('@', $email);

                // Remove and return the last part, which should be the domain
                $domain = array_pop($parts);

                // Check if the domain is in our list
                if ( ! in_array($domain, $allowed))
                {
                    $valid = false;
                }

            }
        }


        if (strlen($password) < 5) { //mirem si es mes llarga que 5

            $valid = false;

        }else{

            if((strtolower($password) != $password && strtoupper($password) != $password)){ //mirem si hi ha majuscules i minuscules


                if (!(preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $password))) //mirem si hi ha numeros i lletres
                {
                    $valid = false;
                }
            }
        }


        if($valid == true){


          try {

              $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
          //    $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

              $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

              $statement = $db->query("SELECT USER.password FROM USER WHERE email LIKE '$email' AND verified = true" );
              $statement->execute();
              $info = $statement->fetch();


              if(password_verify($password, $info[0])) {

                  $error = "WELCOME " . $email . "!";

                  $_SESSION['login'] = $email;
                  $_SESSION['is_login'] = true;
                  $success = "You are now logged in";

                  header("Location: /");


              }else{

                  $error = "Error, try again";
                  $success = "Please Sign In";
              }


            } catch (Exception $e) {

                echo $e;
                $success = "Please Sign In";
            }

        }else{

          $error = "Error, try again";
          $success = "Please Sign In";

        }

        return $this->container->get('view')->render(
            $response,
            'signin.twig',
            [
                'status' => $success,
                'error' => $error,
                'is_login' => isset($_SESSION['is_login']),
            ]
        );
    }

}
