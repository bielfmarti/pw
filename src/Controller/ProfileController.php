<?php

namespace SallePW\SlimApp\Controller;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ProfileController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function upload(Request $request, Response $response): Response
    {
      $target_dir = "uploads/";
      $errorPicture = "";

      if(!empty($_FILES["fileToUpload"]["name"])){

        $errorPicture = "Imagine uploaded!";

        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
          $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
          if($check !== false) {

              $uploadOk = 1;

              if($check[0] > 400){

                $errorPicture = "Imagine width too large.";

                $uploadOk = 0;

              }

              if($check[1] > 400){

                $errorPicture = "Imagine height too large. ";

                $uploadOk = 0;

              }


          } else {
              $errorPicture = "File is not an image. <br>  <br>";
              $uploadOk = 0;
          }
        }
        // Check if file already exists
        if (file_exists($target_file)) {
          $errorPicture = "Sorry, file already exists. ";
          $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 10000000) {
          $errorPicture = "Sorry, your file is too large. ";
          $uploadOk = 0;
        }
        // Allow certain file formats
        if($imageFileType != "png" ) {
          $errorPicture = "Sorry, only PNG & files are allowed. ";
          $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
          $errorPicture = "Sorry, your file was not uploaded. ";
        // if everything is ok, try to upload file
        } else {
            $imageName = 'uploads/'.$_SESSION['login'] . '.png' ;
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $imageName)) {
                $errorPicture = "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded. ";
            } else {
                $errorPicture = "Sorry, there was an error uploading your file. ";
            }
        }

      }


      if(!(empty($_POST['phone']))){

        $phone= $_POST['phone'];

        if (!(preg_match('/(\+34|0034|34)?[ -]*(8|9)[ -]*([0-9][ -]*){8}/', $phone))) //telefono
        {

            $errorPhone = "Good phone";

            $_SESSION["errorPhone"] = $errorPhone;

            $db = new PDO('mysql:host=localhost;dbname=pwpay', "homestead", 'secret');

     //       $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $email = $_SESSION['login'];

            $statement = $db->prepare("UPDATE USER SET USER.phone = :phone WHERE email LIKE '$email'"); //FEM QUE EL TOKEN ESTIGUI COM UTILITZAT
            $statement->bindParam(':phone', $phone, PDO::PARAM_STR);
            $statement->execute();



        }else{

          $errorPhone = "Number should be legal in Spain";

          $_SESSION["errorPhone"] = $errorPhone;

        }


      }else{

        $errorPhone = "";

        $_SESSION["errorPhone"] = $errorPhone;

      }


      $image = 'uploads/'. $_SESSION['login'] .'.png';

      if(!empty($_SESSION['login'])) {

        $image = 'uploads/'. $_SESSION['login'] .'.png';

        if(file_exists($image))
        {
            $welcome = "Welcome to your profile, ".$_SESSION['login']."!" ;

            $foto = "src=uploads/".$_SESSION['login'].".png \n";
        }
        else
        {

          $welcome = "Welcome to your profile, ".$_SESSION['login']."!" ;

          $foto = "src=uploads/nopic.png\n";

        }

        $email = $_SESSION['login'];

        $db = new PDO('mysql:host=localhost;dbname=pwpay', "homestead", 'secret');

     //   $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $db->query("SELECT USER.email, USER.phone, USER.birthday FROM USER WHERE email LIKE '$email'" );
        $statement->execute();
        $info = $statement->fetch();

        $birthday = explode(' ',$info[2]);

        return $this->container->get('view')->render(
            $response,
            'profile.twig',
            [
              'is_login' => isset($_SESSION['is_login']),
              'currentMail' => $info[0],
              'currentPhone' => $info[1],
              'currentBirthday' => $birthday[0],
              'errorPhone' => $_SESSION['errorPhone'],
              'errorPicture' => $errorPicture,
              'welcome' => $welcome,
              'foto' => $foto,
            ]
        );
      }
    }

    public function showProfile(Request $request, Response $response): Response
    {
        if(!empty($_SESSION['login'])) {

          $image = 'uploads/'. $_SESSION['login'] .'.png';

          if(file_exists($image))
          {
            $welcome = "Welcome to your profile, ".$_SESSION['login']."!" ;

            $foto = "src=uploads/".$_SESSION['login'].".png \n";

          }
          else
          {

            $welcome = "Welcome to your profile, ".$_SESSION['login']."!" ;

            $foto = "src=uploads/nopic.png\n";

          }

          $email = $_SESSION['login'];

          $db = new PDO('mysql:host=localhost;dbname=pwpay', "homestead", 'secret');

     //     $db = new PDO('mysql:host=localhost;dbname=pwpay', 'root' );
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $statement = $db->query("SELECT USER.email, USER.phone, USER.birthday FROM USER WHERE email LIKE '$email'" );
          $statement->execute();
          $info = $statement->fetch();

          $birthday = explode(' ',$info[2]);

          return $this->container->get('view')->render(
              $response,
              'profile.twig',
              [
                'is_login' => isset($_SESSION['is_login']),
                'currentMail' => $info[0],
                'currentPhone' => $info[1],
                'currentBirthday' => $birthday[0],
                'welcome' => $welcome,
                'foto' => $foto,
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
