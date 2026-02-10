<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../libs/App.php';

if (!extension_loaded('xmlwriter')) {
    die("System Error: The PHP extension 'xmlwriter' is required but not installed/enabled. Please contact your system administrator to enable it.");
}

$App = new App();
$App->checkAuthentication();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_GET['payperiod'])) {
    $period = $_GET['payperiod'];

    $periodDescs = $App->getPeriodDescription($period);
    $periodDesc = $periodDescs['period'];

    $lists_allows = $App->selectDrop("SELECT tbl_earning_deduction.ed_id, tbl_earning_deduction.edType, tbl_earning_deduction.ed FROM tbl_earning_deduction WHERE edType = 1");
    $lists_deducts = $App->selectDrop("SELECT tbl_earning_deduction.ed_id, tbl_earning_deduction.edType, tbl_earning_deduction.ed FROM tbl_earning_deduction WHERE edType = 2");

    $headings_allow = [];
    $headings_deduc = [];
    foreach ($lists_allows as $lists_allow) {
        $headings_allow[] = $lists_allow['ed'];
    }

    foreach ($lists_deducts as $lists_deduct) {
        $headings_deduc[] = $lists_deduct['ed'];
    }

    // Add standard column headings
    $standardHeadings = ['S/NO', 'Empno', 'Name','Salary Structure','Grade/Step'];
    $standardHeadings = array_merge($standardHeadings, $headings_allow);

    $standardHeadings[] = 'Total Allowance';  // Add "Total Allowance" after allowances
    $standardHeadings = array_merge($standardHeadings, $headings_deduc);

    $standardHeadings[] = 'Total Deduction';  // Add "Total Deduction" after deductions
    $standardHeadings[] = 'Total Net';  // Add "Total Net" at the end

    // Prepare the Spreadsheet
    $spreadsheet = new Spreadsheet();
    $activeWorksheet = $spreadsheet->getActiveSheet();

    // Insert column headings into the first row
    $activeWorksheet->fromArray($standardHeadings, NULL, 'A1');
    $activeWorksheet->getStyle('A1:' . $activeWorksheet->getHighestColumn() . '1')->getFont()->setBold(true);

    // Initialize data array for employee records
    $response['data'] = [];

    $counter = 1;

    // Fetch employee IDs
    try {
        $queryemployee = "SELECT staff_id FROM master_staff WHERE period = ? ORDER BY staff_id ASC";
        $stmt = $App->link->prepare($queryemployee);
        $stmt->execute([$period]);
        $result_employee = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }

    foreach ($result_employee as $row_employee) {
        $thisemployee = $row_employee['staff_id'];

        try {
            $query = $App->link->prepare('SELECT
            tbl_bank.BNAME, 
            tbl_dept.dept, 
            master_staff.STEP, 
            master_staff.GRADE, 
            master_staff.staff_id, 
            master_staff.`NAME`, 
            master_staff.ACCTNO, 
            master_staff.OGNO, 
            employee.EMAIL,tbl_salaryType.SalaryType
        FROM
            master_staff
            LEFT JOIN
            tbl_dept
            ON 
                tbl_dept.dept_id = master_staff.DEPTCD
            LEFT JOIN
            tbl_bank
            ON 
                tbl_bank.BCODE = master_staff.BCODE
            LEFT JOIN
            employee
            ON 
                master_staff.staff_id = employee.staff_id
            LEFT JOIN
            tbl_salaryType
            ON
            master_staff.SALARY_TYPE = tbl_salaryType.salaryType_id
            WHERE master_staff.staff_id = ? AND period = ?');
            $query->execute([$thisemployee, $period]);
            $row_staff = $query->fetch(PDO::FETCH_ASSOC);

            $Data = [
                'S/NO' => $counter,
                'Empno' => $row_staff['OGNO'],
                'Name' => $row_staff['NAME'],
                'Salary Structure' => $row_staff['SalaryType'],
                'Grade/Step' =>$row_staff['GRADE'].'/'.$row_staff['STEP']
            ];

            foreach ($headings_allow as $heading) {
                $Data[$heading] = 0;
            }

            foreach ($headings_deduc as $heading) {
                $Data[$heading] = 0;
            }

            // Fetch allowances
            $query = $App->link->prepare('SELECT tbl_master.allow, tbl_master.deduc, tbl_master.allow_id, tbl_earning_deduction.ed
            FROM tbl_master
            INNER JOIN tbl_earning_deduction ON tbl_master.allow_id = tbl_earning_deduction.ed_id
            WHERE staff_id = ? AND period = ? AND tbl_earning_deduction.edType = 1');
            $query->execute([$thisemployee, $period]);
            $res = $query->fetchAll(PDO::FETCH_ASSOC);

            $total_allow = 0;
            foreach ($res as $link) {
                $edDesc = $link['ed'];
                $amount = $link['allow'] ?? $link['deduc'];
                $Data[$edDesc] = $amount;
                $total_allow += $amount;
            }

            $Data['Total Allowance'] = $total_allow; // Append the Total Allowance

            $total_deduction = 0;
            // Fetch deductions
            $query = $App->link->prepare('SELECT tbl_master.allow, tbl_master.deduc, tbl_master.allow_id, tbl_earning_deduction.ed
            FROM tbl_master
            INNER JOIN tbl_earning_deduction ON tbl_master.allow_id = tbl_earning_deduction.ed_id
            WHERE staff_id = ? AND period = ? AND tbl_earning_deduction.edType = 2');
            $query->execute([$thisemployee, $period]);
            $res = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($res as $link) {
                $edDesc = $link['ed'];
                $amount = $link['deduc'] ?? $link['allow'];
                $Data[$edDesc] = $amount;
                $total_deduction += $amount;
            }

            $Data['Total Deduction'] = $total_deduction; // Append the Total Deduction

            $Data['Total Net'] = $total_allow - $total_deduction; // Calculate and append Total Net

            array_push($response['data'], $Data);
            $counter++;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    }

    // Align the data with headers by reordering the $Data array based on $standardHeadings
    $alignedData = [];
    foreach ($response['data'] as $dataRow) {
        $row = [];
        foreach ($standardHeadings as $header) {
            $row[] = isset($dataRow[$header]) ? $dataRow[$header] : 0;
        }
        $alignedData[] = $row;
    }

    // Insert data into the worksheet
    $activeWorksheet->fromArray($alignedData, null, 'A2');

    // Save the file
    $tempfilepath = $periodDesc.'_gross.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempfilepath);

    // Set headers to force download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $tempfilepath . '"');
    header('Cache-Control: max-age=0');
    header('Content-Length: ' . filesize($tempfilepath));

    // Output the Excel file to the browser
    readfile($tempfilepath);

    // Delete the temporary file
    if (file_exists($tempfilepath)) {
        unlink($tempfilepath);
    }
}
?>
