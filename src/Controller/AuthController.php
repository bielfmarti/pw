<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class AuthController
{
    private  $container;



    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function showAuth(Request $request, Response $response): Response
    {

      $insertedToken = $_GET["token"];

      try {

          // TOKEN EXISTEIX??

          $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
         // $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $statement = $db->query("SELECT TOKEN.id FROM TOKEN WHERE TOKEN.token LIKE '$insertedToken'" );
          $statement->execute();
          $info = $statement->fetch();

          // ------------------

          if(empty($info)) {

              echo "token not found";

          }else{

            // TOKEN UTILITZAT?

            $statement = $db->query("SELECT TOKEN.used FROM TOKEN WHERE token LIKE '$insertedToken'" );
            $statement->execute();
            $info = $statement->fetch();

            if($info[0] == 0){

              // -------------------


              $statement = $db->query("SELECT TOKEN.id_user FROM TOKEN WHERE token LIKE '$insertedToken'" ); //AGAFEM ID DE USER PROPIETARI DEL TOKEN
              $statement->execute();
              $info = $statement->fetch();


              $statement = $db->prepare("UPDATE TOKEN SET TOKEN.used = true WHERE TOKEN.token = :inserted_token"); //FEM QUE EL TOKEN ESTIGUI COM UTILITZAT
              $statement->bindParam(':inserted_token', $insertedToken, PDO::PARAM_STR);
              $statement->execute();


              $statement = $db->prepare("UPDATE USER SET USER.verified = true WHERE USER.id = :user_id"); //FEM QUE EL USER ESTIGUI VERIFICAT
              $statement->bindParam(':user_id', $info[0], PDO::PARAM_STR);
              $statement->execute();

              echo "user verified succesfully";


            }else{

              echo "token already used";

            }

          }

        } catch (PDOException $e) {
            echo $e;
        }

        return $this->container->get('view')->render(
            $response,
            'auth.twig',
            []
        );
    }


}
