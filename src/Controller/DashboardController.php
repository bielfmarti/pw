<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class DashboardController
{
    private  $container;



    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function showDashboard(Request $request, Response $response): Response
    {


      if(!empty($_SESSION['login'])) {

          $email = $_SESSION['login'];


          $db = new PDO('mysql:host=localhost;dbname=pwpay', 'homestead', 'secret' );
        //  $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $statement = $db->query("SELECT USER.money, USER.id FROM USER WHERE email LIKE '$email'" );
          $statement->execute();
          $info = $statement->fetch();

          $money = $info[0];
          $id = $info[1];

          $statement = $db->query("SELECT TRANSACTION.id_sender, TRANSACTION.id_reciever, TRANSACTION.money, TRANSACTION.type FROM TRANSACTION WHERE id_sender LIKE '$id' OR id_reciever LIKE '$id' ORDER BY id DESC LIMIT 5" );
          $statement->execute();
          $info = $statement->fetchAll();

          $i = 0;
          $arg[] = 0;


          while (!empty($info[$i][0])) {
              $arg[$i] = "";
              $arg[$i] .= "Id of the sender: " . $info[$i][0];
              $arg[$i] .= " | Id of the reciever: " . $info[$i][1];
              $arg[$i] .= " | Money: " . $info[$i][2];
              $arg[$i] .= " | Type: " . $info[$i][3];


              $i++;

          }

          return $this->container->get('view')->render(
              $response,
              'dashboard.twig',
              [
                'is_login' => isset($_SESSION['is_login']),
                'money' => $money,
                'message' => $arg,
                'i' => $i,
                'success' => "",

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
