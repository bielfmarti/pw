<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class SendMoneyController
{
    private  $container;



    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sendMoney(Request $request, Response $response): Response
    {

      $email = $_SESSION['login'];

      $sendMoneyTo = $_POST['sendMoneyTo'];
      $sendMoney = $_POST['sendMoney'];

      $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


      $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
      $statement->execute();
      $info = $statement->fetch();

      $idSender = $info[0];

      $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$sendMoneyTo' AND email NOT LIKE '$email' AND verified = 1" );
      $statement->execute();
      $info = $statement->fetch();

      if(empty($info[0])){ //usuario no encontrado

        return $this->container->get('view')->render(
            $response,
            'send-money.twig',
            [
              'is_login' => isset($_SESSION['is_login']),
              'money' => isset($_SESSION['money']),
              'bankAccount' => isset($_SESSION['bankAccount']),
              'errorBank' => "User not found",
            ]
        );

      }else{

        $emailSend = $sendMoneyTo;
        $idReciever = $info[0];

        if( (($_SESSION['money']) >=  $sendMoney) && ($sendMoney > 0)){ //podemos enviar dinero

          $newMoney = $_SESSION['money'] - $sendMoney;

          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $statement = $db->prepare("UPDATE USER SET USER.money= :newMoney WHERE email LIKE '$email'");
          $statement->bindParam(':newMoney', $newMoney, PDO::PARAM_STR);
          $statement->execute();

          $statement = $db->query("SELECT USER.money FROM USER WHERE email LIKE '$emailSend'" );
          $statement->execute();
          $info = $statement->fetch();

          $newMoney = $info[0] + $sendMoney;

          $statement = $db->prepare("UPDATE USER SET USER.money= :newMoney WHERE email LIKE '$emailSend'");
          $statement->bindParam(':newMoney', $newMoney, PDO::PARAM_STR);
          $statement->execute();


          $type = "send";
          $statement = $db->prepare("INSERT INTO transaction (id_sender, id_reciever, money, type) VALUES(:id_sender, :id_reciever, :money, :type)");
          $statement->bindParam(':id_sender', $idSender, PDO::PARAM_STR);
          $statement->bindParam(':id_reciever', $idReciever, PDO::PARAM_STR);
          $statement->bindParam(':money', $sendMoney, PDO::PARAM_STR);
          $statement->bindParam(':type', $type, PDO::PARAM_STR);
          $statement->execute();

        }

      }


      $success = "money sent!";

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


    public function showSendMoney(Request $request, Response $response): Response
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
              'send-money.twig',
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