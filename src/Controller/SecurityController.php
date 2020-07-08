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
          $errorReset = "";
          $email = $_SESSION['login'];

          $db = new PDO('mysql:host=localhost;dbname=pwpay', 'homestead', 'secret' );
          //$db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $statement = $db->query("SELECT USER.password FROM USER WHERE email LIKE '$email'" );
          $statement->execute();
          $info = $statement->fetch();

          $valid = true;

          $oldOld = $_POST['passwordOld'];
          $oldC1 = $_POST['passwordC1'];
          $oldC2 = $_POST['passwordC2'];

          if(!password_verify($_POST['passwordOld'], $info[0])) {

              $errorReset = $errorReset . " Error with password reset (old password doesnt coincide)";
              $oldOld = $_POST['passwordOld'];
              $oldC1 = $_POST['passwordC1'];
              $oldC2 = $_POST['passwordC2'];
              $valid = false;

          }

          if (strlen($_POST['passwordC1']) < 5) { //mirem si es mes llarga que 5

                  $errorReset = "Password needs to be longer than 5 characters. ";
                  $valid = false;

          }

          if(!(strtolower($_POST['passwordC1']) != $_POST['passwordC1'] && strtoupper($_POST['passwordC1']) != $_POST['passwordC1'])){ //mirem si hi ha majuscules i minuscules



              $errorReset = $errorReset . " Password needs mayus and minus.";
              $valid = false;

          }

          if (!(preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $_POST['passwordC1']))) //mirem si hi ha numeros i lletres
          {

              $valid = false;

              $errorReset = $errorReset . " Password needs numbers and letters.";

          }


          if(($_POST['passwordC1'] != $_POST['passwordC2'])){

            $errorReset = $errorReset . " Confirm passwords dont coincide.";
            $valid = false;

          }

          if(!(($_POST['passwordC1'] != $_POST['passwordC2']) || $valid == false)){

            $hashed_pswd  =  password_hash($_POST['passwordC1'], PASSWORD_DEFAULT);

            $statement = $db->prepare("UPDATE USER SET USER.password = :password WHERE email LIKE '$email'"); //FEM QUE EL TOKEN ESTIGUI COM UTILITZAT
            $statement->bindParam(':password', $hashed_pswd, PDO::PARAM_STR);
            $statement->execute();
            $errorReset = $errorReset . " Password reseted succesfully";
            $oldOld = "";
            $oldC1 = "";
            $oldC2 = "";

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
