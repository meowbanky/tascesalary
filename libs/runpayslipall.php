<?php
ini_set('max_execution_time', '0');
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$response = [];
$gross = 0;
$deduction = 0;
$recordtime = date('Y-m-d H:i:s');
$period = $_SESSION['currentactiveperiod'];
$user_id = $_SESSION['SESS_MEMBER_ID'];

$activeEmployees = $App->getActiveEmployees();
$totalEmployees = count($activeEmployees);
$processedEmployees = 0;
?>
<div id="progress" style="border:1px solid #ccc; border-radius: 5px;"></div>
<div id="information" style="width:100%"></div>

<?php

$App->deleteMasterStaff($period);
$App->deleteMaster($period);
foreach ($activeEmployees as $employee) {
    $staff_id = $employee['staff_id'];

//    if ($App->isPayslipAvailable($staff_id, $period)) {

        $App->checkCompleted($staff_id, $period, $user_id);
//    }

    $employeeDetails = $App->getEmployeeDetails($staff_id);
    $empAllowances = $App->getEmployeesEarnings($staff_id, 1);
    $empDeductions = $App->getEmployeesEarnings($staff_id, 2);

    if ($employeeDetails) {
        if($employeeDetails['SALARY_TYPE'] == ''){
            $employeeDetails['SALARY_TYPE'] = -1;
        }
        $App->insertStaffMaster(
            $employeeDetails['STATUSCD'],
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
        if ($empAllowances) {
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

                $App->insertMasterRecordAllow(
                    $staff_id,
                    $empAllowance['allow_id'],
                    $value,
                    1,
                    $period,
                    $recordtime,
                    $user_id
                );

                if (intval($empAllowance['counter']) > 0) {
                    $running_counter = intval($empAllowance['running_counter']);
                    $running_counter = $running_counter + 1;
                    if (($running_counter) == intval($empAllowance['counter'])) {
                        $completedEarnings = $App->completedEarnings($staff_id, $empAllowance['allow_id'], $period, $value, 1);
                        $deleteAllowance = $App->deleteAllowance($staff_id, $empAllowance['allow_id']);
                    } else {
                        $App->updateRunningCounter($running_counter, $staff_id, $empAllowance['allow_id']);
                    }
                }

                $gross += $value;
            }
        }
if($empDeductions) {
    if ($empDeductions) {
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
            $App->insertMasterRecordDeduc(
                $staff_id,
                $empDeduction['allow_id'],
                $value,
                1,
                $period,
                $recordtime,
                $user_id
            );

            if (intval($empDeduction['counter']) > 0) {
                $running_counter = intval($empDeduction['running_counter']);
                $running_counter = $running_counter + 1;
                if (($running_counter) == intval($empDeduction['counter'])) {
                    $completedEarnings = $App->completedEarnings($staff_id, $empDeduction['allow_id'], $period, $value, 1);
                    $deleteAllowance = $App->deleteAllowance($staff_id, $empDeduction['allow_id']);
                } else {
                    $App->updateRunningCounter($running_counter, $staff_id, $empDeduction['allow_id']);
                }
            }
            $deduction += $value;
        }

        $net = $gross - $deduction;
        $response[] = [
            'staff_id' => $staff_id,
            'gross' => $gross,
            'deduction' => $deduction,
            'net' => $net
        ];

        $gross = 0;
        $deduction = 0;
    }
}
    }
    $processedEmployees++;
    $percent = intval($processedEmployees / $totalEmployees * 100). "%";
    echo str_repeat(' ', 1024 * 64);
    echo '<script>
					    parent.document.getElementById("progress").innerHTML="<div style=\"width:' . $percent . ';background:linear-gradient(to bottom, rgba(125,126,125,1) 0%,rgba(14,14,14,1) 100%); text-align:center;color:white;height:35px;display:block;\">' . $percent . '</div>";
					    parent.document.getElementById("information").innerHTML="<div style=\"text-align:center; font-weight:bold\">Processing ' . $employeeDetails['staff_id'] . ' ' . $percent . ' is processed.</div>";</script>';

    ob_flush();
    flush();
}

$status = $App->updatePayPeriodFlag($period);

$response = [
    'status' => 'success',
    'message' => 'Payslip run successfully for all active employees.',
    'data' => $response
];

//echo json_encode($response);
ob_end_flush();
?>
