<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';


$response = [];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$App = new App();


    if(!isset($_POST['dept_id'])){
        $dept_id = -1;
    }else{
        $dept_id = $_POST['dept_id'];
    }

    //$dept_id = trim($_POST['dept_id']);
    $dept = trim($_POST['dept_name']);


    // Validate input
    if (empty($dept)){
        $response = ["status" => "error", "message" => "Dept Name is empty."];
        echo json_encode($response);
        exit();
    }

    $result = $App->create_dept($dept,$dept_id);

    if ($result) {

        $response = ["status" => "success", "message" => "Department created/updated."];
    } else {
        $response = ["status" => "error", "message" => "Error creating/updating Department."];
    }
}
echo json_encode($response);

?>
