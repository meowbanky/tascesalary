<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$response = [];

// Switch to accepting add_grade and add_step
if (isset($_POST['staff_id']) && isset($_POST['add_grade']) && isset($_POST['add_step'])) {
    $staff_id = $_POST['staff_id'];
    $add_grade = (int)$_POST['add_grade'];
    $add_step = (int)$_POST['add_step'];

    // Get Employee Details
    $employeeDetails = $App->getEmployeeDetails($staff_id);

    if ($employeeDetails) {
        $salaryType = $employeeDetails['SALARY_TYPE'];
        if (($salaryType == '') || ($salaryType == null)) {
            $salaryType = -1;
        }

        $currentGrade = (int)$employeeDetails['GRADE'];
        $currentStep = (int)$employeeDetails['STEP'];

        // Calculate New Grade/Step
        $new_grade = $currentGrade + $add_grade;
        $new_step = $currentStep + $add_step;

        // Optional: Enforce minimums or maximums?
        // Usually Grade/Step shouldn't be < 1.
        if ($new_grade < 1) $new_grade = 1;
        if ($new_step < 1) $new_step = 1;
        // Max usually 15, but let's leave it flexible if the system allows higher.

        // Get Current Earnings
        $currentAllowances = $App->getEmployeesEarnings($staff_id, 1);
        
        $estimates = [];
        $totalCurrent = 0;
        $totalNew = 0;

        // Determine New Basic Salary first (Allow ID 1) for Pension calculation
        $newBasicSalaryValue = 0;
        $basicSalaryInfo = $App->getSalaryValue($new_grade, $new_step, $salaryType, 1);
        if ($basicSalaryInfo) {
            $newBasicSalaryValue = $basicSalaryInfo['value'];
        }

        if ($currentAllowances) {
            foreach ($currentAllowances as $allowance) {
                $allow_id = $allowance['allow_id'];
                $currentValue = $allowance['value'];
                $allowDesc = $allowance['ed'];

                $newValue = 0;

                // Lookup new value in salary table
                $salaryValueInfo = $App->getSalaryValue($new_grade, $new_step, $salaryType, $allow_id);
                
                if ($salaryValueInfo) {
                    $newValue = $salaryValueInfo['value'];
                    
                    // Add any extra pay (dept based) if applicable
                    $dept_id = $employeeDetails['DEPTCD'];
                    $extraPay = $App->getExtra($dept_id, $allow_id);
                    if ($extraPay) {
                        $newValue += $extraPay['amount'];
                    }
                } else {
                    // fall back to current value if not found in table
                    $newValue = $currentValue;
                }

                $estimates[] = [
                    'allow_id' => $allow_id,
                    'description' => $allowDesc,
                    'current_value' => $currentValue,
                    'new_value' => $newValue
                ];

                $totalCurrent += $currentValue;
                $totalNew += $newValue;
            }
        }

        $response['status'] = 'success';
        $response['staff_name'] = $employeeDetails['NAME'];
        $response['current_level'] = "Grade $currentGrade, Step $currentStep"; // formatted string
        $response['new_level'] = "Grade $new_grade, Step $new_step"; // formatted string
        $response['current_monthly'] = $totalCurrent;
        $response['new_monthly'] = $totalNew;
        $response['difference'] = $totalNew - $totalCurrent;
        
        // Also ensure specific raw values are passed if needed
        $response['raw_current_grade'] = $currentGrade;
        $response['raw_current_step'] = $currentStep;
        $response['raw_new_grade'] = $new_grade;
        $response['raw_new_step'] = $new_step;


    } else {
        $response['status'] = 'error';
        $response['message'] = 'Employee not found';
    }

} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid parameters';
}

echo json_encode($response);
?>
