<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['delete'])) {
        $deletions = $_POST['delete'];
        $deleteSuccessfully = '';
        // Process each deletion
        foreach ($deletions as $delete) {
            list($temp_id, $staff_id) = explode('/', $delete);
            $array_deletions = [
                ':temp_id' => $temp_id
            ];
            $deleteSuccessfully = $App->selectOne("DELETE FROM allow_deduc WHERE temp_id = :temp_id",$array_deletions);

        }
        echo "Selected allowances have been deleted.";
    }
}

if(isset($_POST['search'])) {
    $staff_id = $_POST['search'];
}else {
//   $staff_id = $_SESSION['staff'];
}
if(isset($staff_id)){
    $employees = $App->getEmployeeDetails($staff_id);
}else{
    $employees = $App->getEmployeeDetails();
}

$selectStatuss = $App->selectDrop("SELECT staff_status.STATUSCD, staff_status.`STATUS`
                                    FROM staff_status");
$selectBanks = $App->selectDrop("SELECT tbl_bank.bank_ID, tbl_bank.BNAME FROM tbl_bank");

$selectDepts = $App->selectDrop("SELECT tbl_dept.dept, tbl_dept.dept_auto FROM tbl_dept");

$selectEmploymentTypes = $App->selectDrop("SELECT tbl_employmenttype.id, tbl_employmenttype.employment_type FROM tbl_employmenttype");

$selectPfas = $App->selectDrop("SELECT tbl_pfa.PFACODE, tbl_pfa.PFANAME FROM tbl_pfa");

$selectSalaryTypes = $App->selectDrop("SELECT tbl_salaryType.salaryType_id, tbl_salaryType.SalaryType FROM tbl_salaryType");


if(isset($staff_id )) {
    $employeePayslip = $App->getEmployeeDetailsPayslip($staff_id, $_SESSION['currentactiveperiod']);

    $empAllowances = $App->getEmployeesEarnings($staff_id, 1);
    $empDeductions = $App->getEmployeesEarnings($staff_id, 2);

    $payslipStatus = $App->isPayslipAvailable($staff_id, $_SESSION['currentactiveperiod']);

    $paySlips = $App->getPaySlip($staff_id, $_SESSION['currentactiveperiod']);
}
?>