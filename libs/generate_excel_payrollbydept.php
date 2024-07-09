<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

$App = new App();
$App->checkAuthentication();
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (isset($_GET['payperiod'])) {
        $period = $_GET['payperiod'];
    }

    if(isset($_GET['dept']) && $_GET['dept'] != ''){
        $dept = $_GET['dept'];
    }else{
        $dept = null;
    }

    $GrossPays = $App->getBankSummaryGroupBy($period, 'master_staff.DEPTCD',$dept);

    $periodDescs = $App->getPeriodDescription($period);
    $periodDesc = $periodDescs['period'];

    if ($GrossPays) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = $dept === null
            ? ['S/N', 'Department Name', 'No of Staff', 'Total Allowance', 'Total Deduction', 'Dept Net']
            : ['S/N', 'Name','Salary Structure', 'Grade', 'Step', 'Total Allowance', 'Total Deduction', 'Net'];

        $sheet->fromArray($headers, NULL, 'A1');

        // Apply bold style to header row
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        // Fill allowance data rows
        $row = 2;
        $gross = 0;
        $deduct = 0;
        $sn = 1;
        $totalStaff = 0;

        foreach ($GrossPays as $GrossPay) {
            $sheet->setCellValue('A' . $row, $sn);
            if($dept == null) {
                $sheet->setCellValue('B' . $row, $GrossPay['dept']);
                $sheet->setCellValue('C' . $row, $GrossPay['staff_count']);
                $sheet->setCellValue('D' . $row, number_format($GrossPay['allow']));
                $sheet->setCellValue('E' . $row, number_format($GrossPay['deduc']));
                $sheet->setCellValue('F' . $row, number_format($GrossPay['allow'] - $GrossPay['deduc']));

            }else{
                $sheet->setCellValue('B' . $row, $GrossPay['NAME']);
                $sheet->setCellValue('C' . $row, $GrossPay['SalaryType']);
                $sheet->setCellValue('D' . $row, $GrossPay['grade']);
                $sheet->setCellValue('E' . $row, $GrossPay['step']);
                $sheet->setCellValue('F' . $row, number_format($GrossPay['allow']));
                $sheet->setCellValue('G' . $row, number_format($GrossPay['deduc']));
                $sheet->setCellValue('H' . $row, number_format($GrossPay['allow'] - $GrossPay['deduc']));

            }

             $row++;
            $sn++;
            $gross += $GrossPay['allow'];
            $deduct += $GrossPay['deduc'];
            $totalStaff += $GrossPay['staff_count'];
        }

        // Set total row
        if($dept == null) {
            $sheet->setCellValue('A' . $row, 'Total');
            $sheet->setCellValue('C' . $row, $totalStaff);
            $sheet->setCellValue('D' . $row, number_format($gross));
            $sheet->setCellValue('E' . $row, number_format($deduct));
            $sheet->setCellValue('F' . $row, number_format($gross - $deduct));
        }else{
            $sheet->setCellValue('A' . $row, 'Total');
            $sheet->setCellValue('F' . $row, number_format($gross));
            $sheet->setCellValue('G' . $row, number_format($deduct));
            $sheet->setCellValue('H' . $row, number_format($gross - $deduct));

        }

        // Apply bold style to total row
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);

        // Generate Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'PayrollByDept_' . $periodDesc . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
    } else {
        echo 'Invalid request. Please provide a valid period.';
    }
}
?>
