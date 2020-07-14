<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Slim\Psr7\Header;


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
            'signup.twig',
            []
        );
    }

    public function registerMe(Request $request, Response $response): Response
    {

      $_SESSION["oldMail"] = "";
      $_SESSION["oldPassword"] = "";

      $_SESSION["oldBirthday"] = "";
      $_SESSION["oldPhone"] = "";

      $_SESSION['error!'] = "prueba";

      $valid = true;

      $errorPassword = "";
      $_SESSION["errorPassword"] = $errorPassword;

      $errorMail = "";
      $_SESSION["errorMail"] = $errorMail;

      $errorPhone = "";

      $errorBirthday = "";


      if (empty($_POST['email']) || empty($_POST['password'])) {

          $errorPassword = $errorPassword . " Introduce a password longer than 6 characters.";
          $_SESSION["errorPassword"] = $errorPassword;

          $valid = false;
      }

      $email = $_POST['email'];

      $password = $_POST['password'];

      $birthday = $_POST['birthday'];

      $phone= $_POST['phone'];

      if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {


              $errorMail = $errorMail . " The email is not valid.";

              $_SESSION['errorMail'] = $errorMail;

              $valid = false;
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

              $errorMail = $errorMail . " The email must be from la salle domain.";

              $_SESSION['errorMail'] = $errorMail;

          }



        $errorMail = "";

        $_SESSION['errorMail'] = $errorMail;


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

              $errorPassword = $errorPassword . " Introduce a password longer than 5 characters";

              $_SESSION["errorPassword"] = $errorPassword;

              $valid = false;

      }

      if(!(strtolower($password) != $password && strtoupper($password) != $password)){ //mirem si hi ha majuscules i minuscules

          $valid = false;

          $errorPassword = $errorPassword . " Introduce a password with minus and majus";

          $_SESSION["errorPassword"] = $errorPassword;

      }

      if (!(preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $password))) //mirem si hi ha numeros i lletres
      {

          $valid = false;

          $errorPassword = $errorPassword . " Introduce a password with letters and numbers.";

          $_SESSION["errorPassword"] = $errorPassword;

      }

      $currentDate = date("d-m-Y");

      $diff = abs(strtotime($birthday) - strtotime($currentDate));

      $years = floor($diff / (365*60*60*24));

      if(empty($_POST['birthday'])){

        $errorBirthday = "Introduce age (must be +18)";

        $_SESSION["errorBirthday"] = $errorBirthday;

      }

      if(!($years >= 18) ){

        $valid = false;

        $errorBirthday = "You must be 18 years or older";

        $_SESSION["errorBirthday"] = $errorBirthday;

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

      $_SESSION["errorMail"] = $errorMail;

      $_SESSION["errorPassword"] = $errorPassword;

      $_SESSION["errorBirthday"] = $errorBirthday;

      $_SESSION["errorPhone"] = $errorPhone;


      if($valid == true){

          $password_hashed =  password_hash($password, PASSWORD_DEFAULT);
          try {

              $db = new PDO('mysql:host=localhost;dbname=pwpay', 'homestead', 'secret' );
             // $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

              $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

              $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
              $statement->execute();
              $info = $statement->fetch();

              if(!empty($info)) {

                  $_SESSION["errorMail"] = "Last user you typed already exists and you cannot register";

                  $errorMail = $errorMail . " Last user you typed already exists and you cannot register.";

                  $valid = false;

              }else{

                  try {

                      $db = new PDO('mysql:host=localhost;dbname=pwpay', 'homestead', 'secret' );
                     // $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );

                      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


                      $statement = $db->prepare("INSERT INTO USER (email, password, birthday, phone) VALUES(:mail, :pass, :birthday, :phone)");
                      $filteredMail= filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                      $filteredPass = filter_var($password_hashed, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                      $statement->bindParam(':mail', $filteredMail, PDO::PARAM_STR);
                      $statement->bindParam(':pass', $filteredPass, PDO::PARAM_STR);
                      $statement->bindParam(':birthday', $birthday, PDO::PARAM_STR);
                      $statement->bindParam(':phone', $phone, PDO::PARAM_STR);
                      $statement->execute();

                      $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
                      $statement->execute();
                      $info = $statement->fetch();

                      $t = bin2hex(random_bytes(10));

                      $statement = $db->prepare("INSERT INTO TOKEN (id_user, token) VALUES(:id_user, :token)");
                      $statement->bindParam(':token', $t, PDO::PARAM_STR);
                      $statement->bindParam(':id_user', $info[0], PDO::PARAM_STR);
                      $statement->execute();

                      $mail = new PHPMailer(true);

                      $_SESSION["errorPhone"] = "Activation mail sent to " . $email;

                      try {
                          //Server settings
                          //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                          $mail->isSMTP();                                            // Send using SMTP
                          $mail->Host       = 'mail.smtpbucket.com';                    // Set the SMTP server to send through
                          $mail->Port       = 8025;                                    // TCP port to connect to

                          //Recipients
                          $mail->setFrom('g18@pwpay.com', 'Mailer');
                          $mail->addReplyTo('g18@pwpay.com', 'Information');
                          $mail->addCC($email);

                          // Content
                          $mail->isHTML(true);                                  // Set email format to HTML
                          $mail->Subject = 'Activation link for ' . $email;

                          $mail->Body = 'Click this link for activation: http://localhost/activate?token=' . $t ."\r\n (Potser s'ha de canviar el \"localhost\" a la direcció assignada al teu PC)";

                          $mail->send();

                          $_SESSION["errorMail"] = "";
                          $_SESSION["errorPassword"] = "";
                          $_SESSION["errorBirthday"] = "";

                          $_SESSION["errorPhone"] = "Activation mail sent to " . $email;

                      } catch (Exception $e) {
                          echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                      }



                  } catch (PDOException $e) {
                      $errorPassword = $e;
                      $_SESSION["errorPassword"] = $errorPassword;
                  }


              }

          } catch (PDOException $e) {
              $_SESSION["errorPassword"] = $e;
          }



      }

      $_SESSION["errorMail"] = $errorMail;

      $_SESSION["errorPassword"] = $errorPassword;

      $_SESSION["errorBirthday"] = $errorBirthday;

      $_SESSION["errorPhone"] = $errorPhone;

      $_SESSION["oldMail"] = $email;

      $_SESSION["oldPassword"] = $password;

      $_SESSION["oldBirthday"] = $birthday;

      $_SESSION["oldPhone"] = $phone;

      if($valid == true){

        return $this->container->get('view')->render(
            $response,
            'signup.twig',
            [
                'errorPassword' => "",
                'errorMail' => "",
                'errorBirthday' => "",
            //    'errorPhone' => "Succesfully registered!",
                'errorPhone' => "Check your email to verify the account",
                'oldMail' => $_SESSION['oldMail'],
                'oldPassword' => $_SESSION['oldPassword'],
                'oldBirthday' => $_SESSION['oldBirthday'],
                'oldPhone' => $_SESSION['oldPhone'],
            ]
        );

      }else{

        return $this->container->get('view')->render(
            $response,
            'signup.twig',
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

}
