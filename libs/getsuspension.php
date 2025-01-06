<?php
require_once 'App.php';

$App = new App;

$recordtime = date('Y-m-d H:i:s');
$staff_id = $_POST['staff_id'];
$suspension_factor = $_POST['suspension_factor'];


$step = $_POST['step'];

// Check if 'P' is already present in the step
if (strpos($step, 'S') === false) {
    // If 'P' is not present, append 'S'
    $step .= 'S';
}


if(isset($suspension_factor) && !isset($_POST['finalise'])) {

    $allowances = $App->getEmployeesEarnings($staff_id,1);

    if ($allowances) {
        foreach ($allowances as $allowance) {
            $suspendedValue = $App->calculateSuspendedValue($allowance['value'],$suspension_factor);
            $App->deleteProrateAllowance($staff_id, $allowance['allow_id']);
            $App->insertProrateAllowance($staff_id, $allowance['allow_id'], $suspendedValue, $allowance['edType'], $_SESSION['SESS_MEMBER_ID'], $recordtime);
        }
            $type = 1;
        $updatedAllowances = $App->getProratedAllowances($staff_id,$type);
        $App->displayAllowances($updatedAllowances);
    } else {
        echo "No Record Found";
    }
}

if(isset($_POST['finalise'])) {
    $updatedAllowances =  $updatedAllowances = $App->getProratedAllowances($staff_id, 1);
    if($updatedAllowances) {
        foreach ($updatedAllowances as $allowance) {
            $App->deleteAllowance($staff_id, $allowance['allow_id']);
            $App->insertProratedAllow($staff_id, $allowance['allow_id'], $allowance['value']);
        }
        $delete = $App->deleteProrate($staff_id);
        $message = $App->updateEmployeeProrate($staff_id, $step);

        if ($message) {
            echo 'success';
        }
    }else{
        echo 'Error calculating suspended salary';
    }


}
?>
