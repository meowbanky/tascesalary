<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$response = [];
$error = [];
$gross = 0;
$deduction = 0;
$staff_id = $_POST['staff_id'];
$recordtime = date('Y-m-d H:i:s');
$period = $_SESSION['currentactiveperiod'];
$user_id = $_SESSION['SESS_MEMBER_ID'];

// Check if payslip is available and handle deletion of previous data
if ($App->isPayslipAvailable($staff_id, $period)) {
    $App->deleteMasterStaff($staff_id, $period);
    $App->deleteMaster($staff_id, $period);
    $App->checkCompleted($staff_id, $period, $user_id);
}

// Get employee details and earnings/deductions
$employeeDetails = $App->getEmployeeDetails($staff_id);
$empAllowances = $App->getEmployeesEarnings($staff_id, 1);
$empDeductions = $App->getEmployeesEarnings($staff_id, 2);

if ($employeeDetails) {
    // Insert staff master data
    if(($employeeDetails['SALARY_TYPE'] == '')||($employeeDetails['SALARY_TYPE'] ==null)){
        $employeeDetails['SALARY_TYPE'] = -1;
    }
    $statusCd = $employeeDetails['STATUSCD'] ?? 'A';
    $App->insertStaffMaster(
        $statusCd,
        $employeeDetails['SALARY_TYPE'],
        $employeeDetails['OGNO'],
        $employeeDetails['staff_id'],
        $employeeDetails['NAME'],
        $employeeDetails['DEPTCD'],
        $employeeDetails['BANK_ID'],
        $employeeDetails['ACCTNO'],
        $employeeDetails['GRADE'],
        $employeeDetails['STEP'],
        $period,
        $employeeDetails['PFACODE'],
        $employeeDetails['PFAACCTNO']
    );

    // Process allowances
    if($empAllowances) {
        foreach ($empAllowances as $empAllowance) {
            $salaryValue = $App->getSalaryValue(
                $employeeDetails['GRADE'],
                $employeeDetails['STEP'],
                $employeeDetails['SALARY_TYPE'],
                $empAllowance['allow_id']
            );
            $dept_id = $employeeDetails['DEPTCD'];
            $allow_id = $empAllowance['allow_id'];
            $extraPay = $App->getExtra($dept_id, $allow_id);
            $extraAmount = 0;

            if($extraPay) {
                $extraAmount = $extraPay['amount'];
            }

            $baseSalaryValue = $salaryValue['value'] ?? 0; // Ensure $baseSalaryValue is set correctly
            $salaryValue = $baseSalaryValue + $extraAmount;
            $value = $salaryValue ? $salaryValue : $empAllowance['value'];

            $App->updateAllowances($value, $user_id, $staff_id, $empAllowance['allow_id']);
            $App->insertMasterRecordAllow($staff_id, $empAllowance['allow_id'], $value, 1, $period, $recordtime, $user_id);

            // Handle counter for allowances
            if (intval($empAllowance['counter']) > 0) {
                $running_counter = intval($empAllowance['running_counter']) + 1;
                if ($running_counter == intval($empAllowance['counter'])) {
                    $App->completedEarnings($staff_id, $empAllowance['allow_id'], $period, $value, 1);
                    $App->deleteAllowance($staff_id, $empAllowance['allow_id']);
                } else {
                    $App->updateRunningCounter($running_counter, $staff_id, $empAllowance['allow_id']);
                }
            }
            $gross += $value;
        }
    }else {
        $gross = 0;
    }

    // Process deductions
    if($empDeductions) {
        foreach ($empDeductions as $empDeduction) {
            if ((int)$empDeduction['allow_id'] == 25) {
                $basicSalary = $App->getSalaryValue(
                    $employeeDetails['GRADE'],
                    $employeeDetails['STEP'],
                    $employeeDetails['SALARY_TYPE'],
                    1
                );
                if (($basicSalary !== null) && ($basicSalary)) {
                    $pensionValue = (int)(floatval($basicSalary['value']) * 0.075); // Calculate 7.5% of basic salary
                } else {
                    $pensionValue = $empDeduction['value']; // Handle case where basic salary is not found
                }
                $value = $pensionValue;
            } else {
                $salaryValue = $App->getSalaryValue(
                    $employeeDetails['GRADE'],
                    $employeeDetails['STEP'],
                    $employeeDetails['SALARY_TYPE'],
                    $empDeduction['allow_id']
                );
                $value = $salaryValue ? (int)($salaryValue) : $empDeduction['value'];
            }

            $App->updateAllowances($value, $user_id, $staff_id, $empDeduction['allow_id']);
            $App->insertMasterRecordDeduc($staff_id, $empDeduction['allow_id'], $value, 1, $period, $recordtime, $user_id);

            // Handle counter for deductions
            if (intval($empDeduction['counter']) > 0) {
                $running_counter = intval($empDeduction['running_counter']) + 1;
                if ($running_counter == intval($empDeduction['counter'])) {
                    $App->completedEarnings($staff_id, $empDeduction['allow_id'], $period, $value, 1);
                    $App->deleteAllowance($staff_id, $empDeduction['allow_id']);
                } else {
                    $App->updateRunningCounter($running_counter, $staff_id, $empDeduction['allow_id']);
                }
            }
            $deduction += $value;
        }
    }else{
        $deduction = 0;
    }

    $net = $gross - $deduction;

    // Prepare response based on net value
    if ($net <= 0) {
        $response['staff_id'] = $staff_id;
        $response['net'] = $net;
    }

    $response['status'] = 'success';
    $response['message'] = 'Payslip run successfully';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Employee details not found';
}

echo json_encode($response);
?>
