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
    $bank_code = trim($_POST['bank_code']);
    $bank_name = trim($_POST['bank_name']);


    // Validate input
    if (empty($bank_code)){
        $response = ["status" => "error", "message" => "Bank Code is empty."];
        echo json_encode($response);
        exit();
    }
    if(empty($bank_name))  {
         $response = ["status" => "error", "message" => "Bank Name is required."];
        echo json_encode($response);
        exit();
    }




    $result = $App->create_bank((int)$bank_code, $bank_name);

    if ($result) {

        $response = ["status" => "success", "message" => "Bank created/updated."];
    } else {
        $response = ["status" => "error", "message" => "Error creating/updating Bank."];
    }
}
echo json_encode($response);

?>
