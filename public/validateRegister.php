<?php



session_start();


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
    header("location: register.php");
    $valid = false;
}

$email = $_POST['email'];

$password = $_POST['password'];




if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {


        $errorMail = "The email is not valid";
        $_SESSION["errorMail"] = $errorMail;
        header("location: register.php");

        $valid = false;
}

if (strlen($password) < 6) {
        $errorPassword = "Introduce a password longer than 6 characters";
        $_SESSION["errorPassword"] = $errorPassword;
        header("location: register.php");

        $valid = false;
}

if($valid == true){



    try {
        $db = new PDO('mysql:host=localhost;dbname=test', 'root' );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $statement = $db->query("SELECT USER.id FROM USER WHERE email LIKE '$email'" );
        $statement->execute();
        $info = $statement->fetch();

        if(!empty($info)) {
            $_SESSION["errorPassword"] = "Last user you typed already exists cannot register";
            

        }else{

            try {
                $db = new PDO('mysql:host=localhost;dbname=test', 'root' );
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $currentDate = date("Y-m-d H:i:s");
                $statement = $db->prepare("INSERT INTO user (email, password, created_at) VALUES(:mail, :pass, :cdate)");
                $filteredMail= filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $filteredPass = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $statement->bindParam(':mail', $filteredMail, PDO::PARAM_STR);
                $statement->bindParam(':pass', $filteredPass, PDO::PARAM_STR);
                $statement->bindParam(':cdate', $currentDate, PDO::PARAM_STR);
                $statement->execute();
                $success = true;


                header("location: login.php");

            } catch (PDOException $e) {
                $errorPassword = $e;
                $_SESSION["errorPassword"] = $errorPassword;
                header("location: register.php");
            }


        }

    } catch (PDOException $e) {
        $_SESSION["errorPassword"] = $e;
        header("location: register.php");
    }



}

?>
