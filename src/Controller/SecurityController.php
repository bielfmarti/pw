<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class SecurityController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function reset(Request $request, Response $response): Response
    {
      try {

          $email = $_SESSION['login'];

          $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
          //$db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $statement = $db->query("SELECT USER.password FROM USER WHERE email LIKE '$email'" );
          $statement->execute();
          $info = $statement->fetch();

          $valid = true;

          if($_POST['passwordOld'] == $info[0]) {

              if (strlen($_POST['passwordC1']) < 5) { //mirem si es mes llarga que 5

                      $errorReset = "Introduce a password longer than 5 characters";

                      $valid = false;

              }else{

                if(!(strtolower($_POST['passwordC1']) != $_POST['passwordC1'] && strtoupper($_POST['passwordC1']) != $_POST['passwordC1'])){ //mirem si hi ha majuscules i minuscules

                    $valid = false;

                    $errorReset = "Password not hard enough (need mayus and minus)";


                }else{

                  if (!(preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $_POST['passwordC1']))) //mirem si hi ha numeros i lletres
                  {

                      $valid = false;

                      $errorReset = "Password not hard enough (need mayus and minus)";

                  }else{

                    $errorReset = "Password not hard enough (need mayus and minus)";

                  }
                }
              }
              if(($_POST['passwordC1'] != $_POST['passwordC2'])){

                $errorReset = "Error with password reset (confirm passwords dont coincide)";

              }

              if(!(($_POST['passwordC1'] != $_POST['passwordC2']) || $valid == false)){

                $statement = $db->prepare("UPDATE USER SET USER.password = :password WHERE email LIKE '$email'"); //FEM QUE EL TOKEN ESTIGUI COM UTILITZAT
                $statement->bindParam(':password', $_POST['passwordC1'], PDO::PARAM_STR);
                $statement->execute();
                $errorReset = "Password reseted succesfully";
                $oldOld = "";
                $oldC1 = "";
                $oldC2 = "";

              }

          }else{

              $errorReset = "Error with password reset (old password doesnt coincide)";
              $oldOld = $_POST['passwordOld'];
              $oldC1 = $_POST['passwordC1'];
              $oldC2 = $_POST['passwordC2'];

          }


        } catch (Exception $e) {

            echo $e;
            $success = "Please Sign In";
        }

        return $this->container->get('view')->render(
            $response,
            'security.twig',
            [
              'is_login' => isset($_SESSION['is_login']),
              'errorReset' => $errorReset,
              'oldOld' => $oldOld,
              'oldC1' => $oldC1,
              'oldC2' => $oldC2,
            ]
        );
    }

    public function showSecurity(Request $request, Response $response): Response
    {
        if(!empty($_SESSION['login'])) {

          return $this->container->get('view')->render(
              $response,
              'security.twig',
              [
                'is_login' => isset($_SESSION['is_login']),
                'oldOld' => "",
                'oldC1' => "",
                'oldC2' => "",
              ]
          );
        }else{

          return $this->container->get('view')->render(
              $response,
              'visits.twig',
              [

              ]
          );

        }
    }
}
