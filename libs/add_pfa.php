<?php
require_once 'App.php';
$App = new App();
require '../vendor/autoload.php';
require '../config/config.php';
$App->checkAuthentication();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = [];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$App = new App();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

}
    $pfacode = trim($_POST['pfacode']);
    $pfaname = trim($_POST['pfaname']);

    if(empty($pfaname))  {
         $response = ["status" => "error", "message" => "PFA Name is required."];
        echo json_encode($response);
        exit();
    }




    $result = $App->create_pfa((int)$pfacode, $pfaname);

    if ($result) {

        $response = ["status" => "success", "message" => "PFA created/updated."];
    } else {
        $response = ["status" => "error", "message" => "Error creating/updating PFA."];
    }
}
echo json_encode($response);

?>
