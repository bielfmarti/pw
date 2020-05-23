<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class TransactionsController
{
    private  $container;



    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showTransactions(Request $request, Response $response): Response
    {
      if(!empty($_SESSION['login'])) {

        $email = $_SESSION['login'];

        $db = new PDO('mysql:host=localhost;dbname=pwpay', 'homestead', 'secret' );
      //  $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
        $statement->execute();
        $info = $statement->fetch();

        $userId = $info[0];

        $statement = $db->query("SELECT TRANSACTION.type, TRANSACTION.id_sender, TRANSACTION.id_reciever, TRANSACTION.money FROM TRANSACTION WHERE TRANSACTION.id_reciever LIKE '$userId' OR TRANSACTION.id_sender LIKE '$userId' ORDER BY id DESC" );
        $statement->execute();
        $info = $statement->fetchAll();

        $i = 0;

        $message[] = 0;

        while (!empty($info[$i][0])) {


            if($info[$i][0] == "load"){

              $message[$i] = "-You loaded " . $info[$i][3] . "$";
            }

            if($info[$i][0] == "send"){

              $message[$i] = "-You sent " . $info[$i][3]. "$ to " . $info[$i][2];
            }

            if($info[$i][0] == "acceptedRequest" && $info[$i][1] == $userId){

              $message[$i] = "-You requested " . $info[$i][3]. "$ from " . $info[$i][2]. " and he/she accepted";
            }

            if($info[$i][0] == "pendingRequest" && $info[$i][1] == $userId){

              $message[$i] = "-You requested " . $info[$i][3]. "$ from " . $info[$i][2]. " and he/she hasn't accepted yet";
            }

            $i++;

        }

        return $this->container->get('view')->render(
            $response,
            'transactions.twig',
            [
                'is_login' => isset($_SESSION['is_login']),
                'message' => $message,
                'i' => $i,
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
