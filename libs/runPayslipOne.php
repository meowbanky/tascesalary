<?php

require 'App.php';

$App = new App;

$response = [];
$staff_id = $_POST['staff_id'];
$recordtime = date('Y-m-d H:i:s');
$period = $_SESSION['currentactiveperiod'];
$user_id = $_SESSION['SESS_MEMBER_ID'];
if($App->isPayslipAvailable($staff_id, $period)) {
    $App->deleteMasterStaff($staff_id, $period);
    $App->deleteMaster($staff_id, $period);
    $App->checkCompleted($staff_id, $period, $user_id);
}


$employeeDetails = $App->getEmployeeDetails($staff_id);

$empAllowances = $App->getEmployeesEarnings($staff_id,1);
$empDeductions = $App->getEmployeesEarnings($staff_id,2);



if($employeeDetails) {
    $App->insertStaffMaster($employeeDetails['staff_id'], $employeeDetails['NAME'], $employeeDetails['DEPTCD'],
        $employeeDetails['BANK_ID'], $employeeDetails['ACCTNO'], $employeeDetails['GRADE'], $employeeDetails['STEP'],
        $period, $employeeDetails['PFACODE'], $employeeDetails['PFAACCTNO']);

    foreach ($empAllowances as $empAllowance) {
        $salaryValue = $App->getSalaryValue($employeeDetails['GRADE'], $employeeDetails['STEP'], $employeeDetails['SALARY_TYPE'], $empAllowance['allow_id']);

        if ($salaryValue) {
            $value = $salaryValue['value'];
        } else {
            $value = $empAllowance['value'];

        }
        $App->updateAllowances($value, $user_id, $staff_id, $empAllowance['allow_id']);

        $App->insertMasterRecordAllow($staff_id, $empAllowance['allow_id'], $value, 1, $period, $recordtime, $user_id);

        if (intval($empAllowance['counter']) > 0) {
            $running_counter = intval($empAllowance['running_counter']);
            $running_counter = $running_counter + 1;
            if (($running_counter) == intval($empAllowance['counter'])) {

                $completedEarnings = $App->completedEarnings($staff_id, $empAllowance['allow_id'], $period, $value, 1);
                // Delete completedEarnings
                $deleteAllowance = $App->deleteAllowance($staff_id, $empAllowance['allow_id']);
            } else {
                $App->updateRunningCounter($running_counter, $staff_id, $empAllowance['allow_id']);

            }
        }
    }

    foreach ($empDeductions as $empDeduction) {
        $salaryValue = $App->getSalaryValue($employeeDetails['GRADE'], $employeeDetails['STEP'], $employeeDetails['SALARY_TYPE'], $empAllowance['allow_id']);

        if ($salaryValue) {
            $value = $salaryValue['value'];
        } else {
            $value = $empDeduction['value'];
        }
        $App->updateAllowances($value, $user_id, $staff_id, $empDeduction['allow_id']);
        $App->insertMasterRecordDeduc($staff_id, $empDeduction['allow_id'], $value, 1, $period, $recordtime, $user_id);

        if (intval($empDeduction['counter']) > 0) {
            $running_counter = intval($empDeduction['running_counter']);
            $running_counter = $running_counter + 1;
            if (($running_counter) == intval($empDeduction['counter'])) {

                $completedEarnings = $App->completedEarnings($staff_id, $empDeduction['allow_id'], $period, $value, 1);
                // Delete completedEarnings
                $deleteAllowance = $App->deleteAllowance($staff_id, $empDeduction['allow_id']);
            } else {
                $App->updateRunningCounter($running_counter, $staff_id, $empDeduction['allow_id']);

            }
        }
    }
}

$response['status'] = 'success';
$response['message'] = 'Payslip run successfully';

echo json_encode($response);
