<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class RequestMoneyController
{
    private  $container;



    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function requestMoney(Request $request, Response $response): Response
    {
      $email = $_SESSION['login'];
      $requestMoneyTo = $_POST['requestMoneyTo'];
      $requestMoney = $_POST['requestMoney'];

      $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


      $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
      $statement->execute();
      $info = $statement->fetch();

      $idSender = $info[0];



      $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$requestMoneyTo' AND email NOT LIKE '$email' AND USER.verified = true" );
      $statement->execute();
      $info = $statement->fetch();

      if(empty($info[0])){ //usuario no encontrado

        return $this->container->get('view')->render(
            $response,
            'request-money.twig',
            [
              'is_login' => isset($_SESSION['is_login']),
              'money' => isset($_SESSION['money']),
              'bankAccount' => isset($_SESSION['bankAccount']),
              'errorBank' => "User not found",
            ]
        );

      }else{

        $email = $_SESSION['login'];

        $type = "pendingRequest";

        $statement = $db->prepare("INSERT INTO transaction (id_sender, id_reciever, money, type) VALUES(:id_sender, :id_reciever, :money, :type)");
        $statement->bindParam(':id_sender', $idSender, PDO::PARAM_STR);
        $statement->bindParam(':id_reciever', $info[0], PDO::PARAM_STR);
        $statement->bindParam(':money', $requestMoney, PDO::PARAM_STR);
        $statement->bindParam(':type', $type, PDO::PARAM_STR);
        $statement->execute();

        $success = "money requested!";

        return $this->container->get('view')->render(
            $response,
            'dashboard.twig',
            [
              'is_login' => isset($_SESSION['is_login']),
              'money' => "",
              'transactions' => "",
              'success' => $success,
            ]
        );


      }

    }

    public function accept(Request $request, Response $response): Response
    {


      if(!empty($_SESSION['login'])) {

        $email = $_SESSION['login'];

        $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
        $statement->execute();
        $info = $statement->fetch();

        $idUser = $info[0];
        $dummy = $request->getAttribute('id');

        $statement = $db->query("SELECT TRANSACTION.id, TRANSACTION.money, TRANSACTION.id_sender FROM TRANSACTION WHERE TRANSACTION.id = '$dummy' AND id_reciever LIKE '$idUser' AND type LIKE 'pendingRequest'" );
        $statement->execute();
        $info = $statement->fetch();

        if(empty($info[0])){//not found

          echo "Request not found";

          return $this->container->get('view')->render(


              $response,
              'pending.twig',
              [
                'is_login' => isset($_SESSION['is_login']),
                'bankAccount' => isset($_SESSION['bankAccount']),
              ]
          );

        }else{

          $requiredMoney = $info[1];

          $idSender = $info[2];

          $statement = $db->query("SELECT USER.money FROM USER WHERE USER.email = '$email'" );
          $statement->execute();
          $info = $statement->fetch();

          $currentMoney = $info[0];

          if($currentMoney >= $requiredMoney){ //we accept transaction

            $moneySubstracted = $currentMoney - $requiredMoney;

            $statement = $db->prepare("UPDATE USER SET USER.money = :moneySubstracted WHERE USER.email = '$email'"); //bajamos dinero
            $statement->bindParam(':moneySubstracted', $moneySubstracted, PDO::PARAM_STR);
            $statement->execute();

            $statement = $db->query("SELECT USER.money FROM USER WHERE USER.id = '$idSender'" ); //miramos dinero actual del solicitante
            $statement->execute();
            $info = $statement->fetch();

            $moneyAdded = $info[0] + $requiredMoney;

            $statement = $db->prepare("UPDATE USER SET USER.money = :moneyAdded WHERE USER.id = '$idSender'");
            $statement->bindParam(':moneyAdded', $moneyAdded, PDO::PARAM_STR);
            $statement->execute();

            $acceptedRequest = "acceptedRequest";
            $statement = $db->prepare("UPDATE TRANSACTION SET TRANSACTION.type = :transaction WHERE id LIKE '$dummy'");
            $statement->bindParam(':transaction', $acceptedRequest, PDO::PARAM_STR);
            $statement->execute();

            echo "Request accepted and money sent";

            return $this->container->get('view')->render(
                $response,
                'pending.twig',
                [
                  'is_login' => isset($_SESSION['is_login']),
                  'bankAccount' => isset($_SESSION['bankAccount']),
                ]
            );

          }else{

            echo "not enough money";

            return $this->container->get('view')->render(
                $response,
                'pending.twig',
                [
                  'is_login' => isset($_SESSION['is_login']),
                  'bankAccount' => isset($_SESSION['bankAccount']),
                ]
            );

          }


        }

      }else{

        return $this->container->get('view')->render(
            $response,
            'visits.twig',
            [

            ]
        );

      }

    }

    public function showPending(Request $request, Response $response): Response
    {


      if(!empty($_SESSION['login'])) {

        $email = $_SESSION['login'];

        //     $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );


        $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
        $statement->execute();
        $info = $statement->fetch();

        $idUser = $info[0];

        $statement = $db->query("SELECT TRANSACTION.id_sender, TRANSACTION.money, TRANSACTION.id FROM TRANSACTION WHERE id_reciever LIKE '$idUser' AND type LIKE 'pendingRequest' ORDER BY id DESC" );
        $statement->execute();
        $info = $statement->fetchAll();

        $i = 0;
        if(empty($info[0][0])){

          echo "no requests found";

        }

        while (!empty($info[$i][0])) {

            echo "<p style='justify-content: center'>Id of the sender: " . $info[$i][0] . " | Money: " . $info[$i][1] . " | To accept --> " . '<a  href=/account/money/requests/' . $info[$i][2] . '/accept> Click here to accept request</a></p>';
            echo "<br>";
            echo "<br>";
            $i++;

        }

        return $this->container->get('view')->render(
            $response,
            'pending.twig',
            [
              'is_login' => isset($_SESSION['is_login']),
              'money' => "",
              'bankAccount' => isset($_SESSION['bankAccount']),
              'sixDigits' => "",
              'errorBank' => "",
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


    public function showRequestMoney(Request $request, Response $response): Response
    {


      if(!empty($_SESSION['login'])) {

        $email = $_SESSION['login'];

        //     $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );


        $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $db->query("SELECT USER.ibn FROM USER WHERE email LIKE '$email'" );
        $statement->execute();
        $info = $statement->fetch();

        $iban2 = $info[0];

        $sixDigits = $iban2[0] . $iban2[1] . $iban2[2] . $iban2[3] . $iban2[4] . $iban2[5];

        $statement = $db->query("SELECT USER.money FROM USER WHERE email LIKE '$email'" );
        $statement->execute();
        $info = $statement->fetch();

        $money = $info[0];

        if(!empty($info[0])) {

          $bankAccount = true;

        }else{

          $bankAccount = false;
          $money = 0;

        }

        $_SESSION['bankAccount'] = $bankAccount;
        $_SESSION['money'] = $money;

          return $this->container->get('view')->render(
              $response,
              'request-money.twig',
              [
                'is_login' => isset($_SESSION['is_login']),
                'money' => $money,
                'bankAccount' => isset($_SESSION['bankAccount']),
                'sixDigits' => $sixDigits,
                'errorBank' => "",
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
