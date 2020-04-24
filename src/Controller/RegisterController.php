<?php

namespace SallePW\SlimApp\Controller;


use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class RegisterController
{
    private  $container;



    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }



    public function showSignUp(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'sign-up.twig',
            []
        );
    }

    public function registerMe(Request $request, Response $response): Response
    {


      $_SESSION['error!'] = "prueba";

      $valid = true;

      $errorPassword = "";
      $_SESSION["errorPassword"] = $errorPassword;

      $errorMail = "";
      $_SESSION["errorMail"] = $errorMail;


      if (empty($_POST)) {

          exit;
      }

      if (empty($_POST['email']) || empty($_POST['password'])) {

          $errorPassword = "Introduce a password longer than 6 characters";
          $_SESSION["errorPassword"] = $errorPassword;

          $valid = false;
      }

      $email = $_POST['email'];

      $password = $_POST['password'];

      $birthday = $_POST['birthday'];

      $phone= $_POST['phone'];

      if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {


              $errorMail = "The email is not valid";

              $_SESSION['errorMail'] = $errorMail;

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

                $errorMail = "The email must be from la salle domain";

                $_SESSION['errorMail'] = $errorMail;

            }

        }else{

          $errorMail = "";

          $_SESSION['errorMail'] = $errorMail;

        }
      }


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

              $errorMail = "The email must be from la salle domain";

              $_SESSION['errorMail'] = $errorMail;

          }
      }


      if (strlen($password) < 5) { //mirem si es mes llarga que 5

              $errorPassword = "Introduce a password longer than 5 characters";

              $_SESSION["errorPassword"] = $errorPassword;

              $valid = false;

      }else{

        if(!(strtolower($password) != $password && strtoupper($password) != $password)){ //mirem si hi ha majuscules i minuscules

            $valid = false;

            $_SESSION["errorPassword"] = $errorPassword;

        }else{

          if (!(preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $password))) //mirem si hi ha numeros i lletres
          {

              $valid = false;

              $_SESSION["errorPassword"] = $errorPassword;

          }else{

            $_SESSION["errorPassword"] = "";

          }
        }
      }

      $currentDate = date("d-m-Y");

      $diff = abs(strtotime($birthday) - strtotime($currentDate));

      $years = floor($diff / (365*60*60*24));

      if(empty($_POST['birthday'])){

        $errorBirthday = "Introduce age";

        $_SESSION["errorBirthday"] = $errorBirthday;

      }else{

        if(!($years >= 18) ){

          $valid = false;

          $errorBirthday = "You must be 18 years or older";

          $_SESSION["errorBirthday"] = $errorBirthday;

        }else{

          $errorBirthday = "";

          $_SESSION["errorBirthday"] = $errorBirthday;

        }

      }

      if(!(empty($_POST['phone']))){

        if (!(preg_match('/(\+34|0034|34)?[ -]*(8|9)[ -]*([0-9][ -]*){8}/', $phone))) //telefono
        {
            //$valid = false;

            $errorPhone = "Number should be legal in Spain";

            $_SESSION["errorPhone"] = $errorPhone;

        }

      }else{

        $errorPhone = "";

        $_SESSION["errorPhone"] = $errorPhone;
      }


      if($valid == true){

          try {

              $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
              $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

              $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
              $statement->execute();
              $info = $statement->fetch();

              if(!empty($info)) {

                  $_SESSION["errorMail"] = "Last user you typed already exists and you cannot register";

              }else{

                  try {

                      $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
                      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


                      $statement = $db->prepare("INSERT INTO user (email, password, birthday, phone) VALUES(:mail, :pass, :birthday, :phone)");
                      $filteredMail= filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                      $filteredPass = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                      $statement->bindParam(':mail', $filteredMail, PDO::PARAM_STR);
                      $statement->bindParam(':pass', $filteredPass, PDO::PARAM_STR);
                      $statement->bindParam(':birthday', $birthday, PDO::PARAM_STR);
                      $statement->bindParam(':phone', $phone, PDO::PARAM_STR);
                      $statement->execute();
/*
                      $to = 'alexvalletavira@gmail.com';
                      $subject = 'Test email';
                      $message = "Hello World!\n\nThis is my first mail.";
                      $headers = "From: pwmail2020@gmail.com\r\nReply-To: pwmail2020@gmail.com";
                      $mail_sent = @mail( $to, $subject, $message, $headers );
                      echo $mail_sent ? "Mail sent" : "Mail failed";*/
                      $_SESSION["errorMail"] = "";
                      $_SESSION["errorPassword"] = "";
                      $_SESSION["errorBirthday"] = "";
                      $_SESSION["errorPhone"] = "";

                  } catch (PDOException $e) {
                      $errorPassword = $e;
                      $_SESSION["errorPassword"] = $errorPassword;
                  }


              }

          } catch (PDOException $e) {
              $_SESSION["errorPassword"] = $e;
          }



      }else{

        $_SESSION["oldMail"] = $email;
        $_SESSION["oldPassword"] = $password;
        $_SESSION["oldBirthday"] = $birthday;
        $_SESSION["oldPhone"] = $phone;


      }

      return $this->container->get('view')->render(
          $response,
          'sign-up.twig',
          [
              'errorPassword' => $_SESSION['errorPassword'],
              'errorMail' => $_SESSION['errorMail'],
              'errorBirthday' => $_SESSION['errorBirthday'],
              'errorPhone' => $_SESSION['errorPhone'],
              'oldMail' => $_SESSION['oldMail'],
              'oldPassword' => $_SESSION['oldPassword'],
              'oldBirthday' => $_SESSION['oldBirthday'],
              'oldPhone' => $_SESSION['oldPhone'],
          ]
      );
    }

}
