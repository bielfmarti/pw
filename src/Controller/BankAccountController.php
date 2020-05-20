<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Iban\Validation\Validator;
use Iban\Validation\Iban;

final class BankAccountController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function loadMoney(Request $request, Response $response): Response
    {

      $sixDigits = "";
      $money = "";
      $bankAccount = "";
      $moneyAdded = "";
      $errorBank = "";

      if(!empty($_SESSION['login'])) {

          $bankAccount = false;

          $email = $_SESSION['login'];
    //      $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

          $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $statement = $db->query("SELECT USER.ibn FROM USER WHERE email LIKE '$email'" );
          $statement->execute();
          $info = $statement->fetch();

          if(!empty($info[0])) { //si tiene ibn (por lo tanto tiene cuenta)

              $bankAccount = true;

              $iban2 = $info[0];

              $sixDigits = $iban2[0] . $iban2[1] . $iban2[2] . $iban2[3] . $iban2[4] . $iban2[5];

              $statement = $db->query("SELECT USER.money FROM USER WHERE email LIKE '$email'" );
              $statement->execute();
              $info = $statement->fetch();

              $money = $info[0];

              if($_POST['loadMoney'] < 0){

                $errorBank = "money quantity not good";
                $moneyAdded = $money;

              }else{
                $errorBank = "money added";
                $moneyAdded = $money + $_POST['loadMoney'];

                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $statement = $db->prepare("UPDATE USER SET USER.money= :loadMoney WHERE email LIKE '$email'"); //FEM QUE EL TOKEN ESTIGUI COM UTILITZAT
                $statement->bindParam(':loadMoney', $moneyAdded, PDO::PARAM_STR);
                $statement->execute();

                $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
                $statement->execute();
                $info = $statement->fetch();

                $statement = $db->prepare("INSERT INTO transaction (id_sender, id_reciever, money, type) VALUES(:id_sender, :id_reciever, :money, :type)");

                $type = "load";

                $statement->bindParam(':id_sender', $info[0], PDO::PARAM_STR);
                $statement->bindParam(':id_reciever', $info[0], PDO::PARAM_STR);
                $statement->bindParam(':money', $_POST['loadMoney'], PDO::PARAM_STR);
                $statement->bindParam(':type', $type, PDO::PARAM_STR);
                $statement->execute();


                header("Location: /account/bank-account");

              }

          }else{

              $bankAccount = false;
              header("Location: /account/bank-account");

          }

          return $this->container->get('view')->render(
              $response,
              'bank-account.twig',
              [
                'is_login' => isset($_SESSION['is_login']),
                'bankAccount' => $bankAccount,
                'sixDigits' => $sixDigits,
                'errorBank' => $errorBank,
                'money' => $moneyAdded,
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


    public function addAccount(Request $request, Response $response): Response
    {

      $sixDigits = "";

      $money = "";

      $email = $_SESSION['login'];

      $errorBank = "";

      $errorValidation = false;

      $bankAccount = true;

      $iban = new Iban($_POST['ibn']);

      $iban2 = $_POST['ibn'];

      $validator = new Validator();

      if (!$validator->validate($iban)) {
          foreach ($validator->getViolations() as $violation) {
              $errorBank = $violation;
              $errorValidation = true;
          }
      }

      if($errorValidation == false){

        // $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

        $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $statement = $db->prepare("UPDATE USER SET USER.owner_name = :ownerName WHERE email LIKE '$email'"); //FEM QUE EL TOKEN ESTIGUI COM UTILITZAT
        $statement->bindParam(':ownerName', $_POST['ownerName'], PDO::PARAM_STR);
        $statement->execute();

        $statement = $db->prepare("UPDATE USER SET USER.ibn = :ibn WHERE email LIKE '$email'"); //FEM QUE EL TOKEN ESTIGUI COM UTILITZAT
        $statement->bindParam(':ibn', $_POST['ibn'], PDO::PARAM_STR);
        $statement->execute();

        $bankAccount = true;
        $sixDigits = $iban2[0] . $iban2[1] . $iban2[2] . $iban2[3] . $iban2[4] . $iban2[5];

        $statement = $db->query("SELECT USER.money FROM USER WHERE email LIKE '$email'" );
        $statement->execute();
        $info = $statement->fetch();

        $money = $info[0];

      }

      return $this->container->get('view')->render(
          $response,
          'bank-account.twig',
          [
            'is_login' => isset($_SESSION['is_login']),
            'bankAccount' => $bankAccount,
            'errorBank' => $errorBank,
            'sixDigits' => $sixDigits,
            'money' => $money,
          ]
      );

    }

    public function showBankAccount(Request $request, Response $response): Response
    {
      $sixDigits = "";
      $money = "";

      if(!empty($_SESSION['login'])) {

          $bankAccount = false;

          $email = $_SESSION['login'];

      //    $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

          $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $statement = $db->query("SELECT USER.ibn FROM USER WHERE email LIKE '$email'" );
          $statement->execute();
          $info = $statement->fetch();

          if(!empty($info[0])) { //si tiene ibn (por lo tanto tiene cuenta)

              $bankAccount = true;

              $iban2 = $info[0];

              $sixDigits = $iban2[0] . $iban2[1] . $iban2[2] . $iban2[3] . $iban2[4] . $iban2[5];

              $statement = $db->query("SELECT USER.money FROM USER WHERE email LIKE '$email'" );
              $statement->execute();
              $info = $statement->fetch();

              $money = $info[0];

          }else{

              $bankAccount = false;
          }

          return $this->container->get('view')->render(
              $response,
              'bank-account.twig',
              [
                'is_login' => isset($_SESSION['is_login']),
                'bankAccount' => $bankAccount,
                'sixDigits' => $sixDigits,
                'money' => $money,
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
