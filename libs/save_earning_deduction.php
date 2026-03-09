<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ed_id = isset($_POST['ed_id']) ? $_POST['ed_id'] : null;
    $ed = trim($_POST['ed']);
    $type = $_POST['type'];
    $is_retained = isset($_POST['is_retained']) ? 1 : 0;

    if (empty($ed)) {
        $response = ["status" => "error", "message" => "Description is empty."];
        echo json_encode($response);
        exit();
    }

    if ($ed_id === "") {
        $ed_id = null;
    }

    $result = $App->create_earning_deduction($ed, $type, $is_retained, $ed_id);

    if ($result) {
        $response = ["status" => "success", "message" => "Earning/Deduction saved successfully."];
    } else {
        $response = ["status" => "error", "message" => "Error saving Earning/Deduction."];
    }
}
echo json_encode($response);
?>
