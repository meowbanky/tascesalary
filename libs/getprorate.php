<?php
require_once 'App.php';

$App = new App;

$recordtime = date('Y-m-d H:i:s');
$staff_id = $_POST['staff_id'];
$daysToCal = $_POST['daysToCal'];
$currentDays = $_POST['currentDays'];

$step = $_POST['step'];

// Check if 'P' is already present in the step
if (strpos($step, 'P') === false) {
    // If 'P' is not present, append 'P'
    $step .= 'P';
}




if(intval($currentDays) < intval($daysToCal)){
    echo 'Error';
    exit();
}

if(isset($_POST['calculate'])) {

    $allowances = $App->getEmployeesEarnings($staff_id,1);

    if ($allowances) {
        foreach ($allowances as $allowance) {
            $proratedValue = $App->calculateProratedValue($allowance['value'], $daysToCal, $currentDays);
            $App->deleteProrateAllowance($staff_id, $allowance['allow_id']);
            $App->insertProrateAllowance($staff_id, $allowance['allow_id'], $proratedValue, $allowance['edType'], $_SESSION['SESS_MEMBER_ID'], $recordtime);
        }
            $type = 1;
        $updatedAllowances = $App->getProratedAllowances($staff_id,$type);
        $App->displayAllowances($updatedAllowances);
    } else {
        echo "No Record Found";
    }
}

if(isset($_POST['finalise'])) {
    $updatedAllowances = $App->getProratedAllowances($staff_id, 1);
    if ($updatedAllowances) {
        foreach ($updatedAllowances as $allowance) {
            $App->deleteAllowance($staff_id, $allowance['allow_id']);
            $App->insertProratedAllow($staff_id, $allowance['allow_id'], $allowance['value']);
        }
        $delete = $App->deleteProrate($staff_id);
        $message = $App->updateEmployeeProrate($staff_id, $step);

        if ($message) {
            echo 'success';
        }
    } else {
        echo 'No Record Found';
    }
}
?>
