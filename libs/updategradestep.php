<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$response = [];
$array = [];

$recordtime = date('Y-m-d H:i:s');
$staff_id = $_POST['staff_id'];
$newstep = $_POST['newstep'];
$newgrade   = intval($_POST['newgrade']);
$salarytype = $_POST['salarytype'];
$user_id = $_SESSION['SESS_MEMBER_ID'];


$allowances = $App->getEmployeesEarnings($staff_id,$type = 1);

if ($allowances) {
    foreach ($allowances as $allowance) {
        $salaryValue = $App->getSalaryValue($newgrade, $newstep, $salarytype, $allowance['allow_id']);

        if(!$salaryValue){
            $value = 0;
        }else{
            $value = $salaryValue['value'];
        }
        $success = $App->updateAllowances($value, $user_id, $staff_id, $allowance['allow_id']);
        $update = $App->updateGradeStep($newgrade,$newstep,$staff_id);
    }
    echo 'success';
}else{
    echo 'error';
}
