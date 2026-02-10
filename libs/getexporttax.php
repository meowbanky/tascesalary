<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../libs/App.php';

$App = new App();
$App->checkAuthentication();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

if(isset($_GET['payperiod'])) {
    $period = $_GET['payperiod'];

    $lists_allows = $App->selectDrop("SELECT tbl_earning_deduction.ed_id, tbl_earning_deduction.edType, tbl_earning_deduction.ed FROM tbl_earning_deduction WHERE edType = 1");

    $lists_deducts = $App->selectDrop("SELECT tbl_earning_deduction.ed_id, tbl_earning_deduction.edType, tbl_earning_deduction.ed FROM tbl_earning_deduction WHERE edType = 2");


    $headings_allow = [];
    foreach ($lists_allows as $lists_allow) {
        $headings_allow[] = $lists_allow['ed'];
    }

// Add standard column headings
    $standardHeadings = ['S/NO', 'Empno', 'Name'];
    $standardHeadings = array_merge($standardHeadings, $headings_allow);

    $standardHeadings[] = 'Total';  // Add "Total" at the end

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
            master_staff.NAME,
            master_staff.ACCTNO,
            employee.EMAIL
        FROM master_staff
        LEFT JOIN tbl_dept ON tbl_dept.dept_id = master_staff.DEPTCD
        LEFT JOIN tbl_bank ON tbl_bank.BCODE = master_staff.BCODE
        LEFT JOIN employee ON master_staff.staff_id = employee.staff_id
        WHERE master_staff.staff_id = ? AND period = ?');
            $query->execute([$thisemployee, $period]);
            $row_staff = $query->fetch(PDO::FETCH_ASSOC);

            $Data = [
                'S/NO' => $counter,
                'Empno' => $row_staff['staff_id'],
                'Name' => $row_staff['NAME']
            ];

            foreach ($headings_allow as $heading) {
                $Data[$heading] = 0;
            }

            $query = $App->link->prepare('SELECT tbl_master.allow, tbl_master.deduc, tbl_master.allow_id, tbl_earning_deduction.ed
            FROM tbl_master
            INNER JOIN tbl_earning_deduction ON tbl_master.allow_id = tbl_earning_deduction.ed_id
            WHERE staff_id = ? AND period = ? AND tbl_earning_deduction.edType = 1');
            $query->execute([$thisemployee, $period]);
            $res = $query->fetchAll(PDO::FETCH_ASSOC);

            $total = 0;
            foreach ($res as $link) {
                $edDesc = $link['ed'];
                $amount = $link['allow'] ?? $link['deduc'];
                $Data[$edDesc] = $amount;
                $total += $amount;
            }

            $Data['Total'] = $total; // Append the Total at the end

            array_push($response['data'], $Data);
            $counter++;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    }

//echo '<pre>' . print_r($response['data'], true) . '</pre>';
    $activeWorksheet->fromArray($response['data'], null, 'A2');

    $tempfilepath = $_SESSION['activeperiodDescription'] . ' taxExport.xlsx';
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
