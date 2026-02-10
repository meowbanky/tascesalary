<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$response = [];
$staff_id = $_POST['staff_id'];
$recordtime = date('Y-m-d H:i:s');
$period = $_SESSION['currentactiveperiod'];
$user_id = $_SESSION['SESS_MEMBER_ID'];

if($App->isPayslipAvailable($staff_id, $period)) {
    $App->deleteMasterStaff($staff_id, $period);
    $App->deleteMaster($staff_id, $period);
    $App->checkCompleted($staff_id, $period, $user_id);

    $response['status'] = 'success';
    $response['message'] = 'Successfully deleted';

}else{
    $response['status'] = 'error';
    $response['message'] = 'Error deleting Payslip';
}

echo json_encode($response);



