<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../libs/App.php';
$App = new App();
$App->checkAuthentication();
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if(isset($_GET['payperiod'])) {
        $period = $_GET['payperiod'];
    }


    if(isset($_GET['allow_id'])){
        $allow_id =    $_GET['allow_id'];
    }
    if(isset($_GET['type'])){
        $type =    $_GET['type'];
    }

    $Deductions = $App->getReportDeductionList($period, $type,$allow_id);
    $periodDescs = $App->getPeriodDescription($period);
     $periodDesc = $periodDescs['period'];

    $allow_deduc = $type == 1 ? 'Allowance' : 'Deduction';

    if($Deductions ){
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header row
    $headers = ['Allowance/Deduction', 'Staff No', 'Name', 'Amount'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Apply bold style to header row
    $sheet->getStyle('A1:B1')->getFont()->setBold(true);

    $gross = 0;

        $descrip ='';
    // Fill allowance data rows
    $row = 2;
    foreach($Deductions as $Deduction)
        {
            $sheet->setCellValue('A' . $row, $Deduction['edDesc']);
            $sheet->setCellValue('B' . $row, $Deduction['staff_id']);
            $sheet->setCellValue('C' . $row, $Deduction['NAME']);
            $sheet->setCellValue('D' . $row, number_format($Deduction['value']));
            $gross += $Deduction['value'];
            $descrip = $Deduction['edDesc'];
            $row++;
        }

        // Add Gross total and apply bold style
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('D' . $row, number_format($gross));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row++;


        // Generate Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = $descrip.'_' . $periodDesc . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
    }
} else {
    echo 'Invalid request. Please provide a valid period.';
}
?>
