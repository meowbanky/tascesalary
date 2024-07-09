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


    $GrossPays = $App->getBankSummary($period);

    $periodDescs = $App->getPeriodDescription($period);
     $periodDesc = $periodDescs['period'];



    if($GrossPays ){
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header row
    $headers = ['Staff No', 'Name', 'Salary Structure','Department', 'Grade/Step','Account No','Bank','Gross','Deduction','Net'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Apply bold style to header row
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);

    $gross = 0;
    $deduc =0;

        $descrip ='';
    // Fill allowance data rows
    $row = 2;
    foreach($GrossPays as $GrossPay)
        {
            $sheet->setCellValue('A' . $row, $GrossPay['OGNO']);
            $sheet->setCellValue('B' . $row, $GrossPay['NAME']);
            $sheet->setCellValue('C' . $row, $GrossPay['SalaryType']);
            $sheet->setCellValue('D' . $row, $GrossPay['dept']);
            $sheet->setCellValue('E' . $row, $GrossPay['grade'].'/'.$GrossPay['step']);
            $sheet->setCellValue('F' . $row, $GrossPay['acctno']);
            $sheet->setCellValue('G' . $row, $GrossPay['bankname']);
            $sheet->setCellValue('H' . $row, number_format($GrossPay['allow']));
            $sheet->setCellValue('I' . $row, number_format($GrossPay['deduc']));
            $sheet->setCellValue('J' . $row, number_format($GrossPay['allow']-$GrossPay['deduc']));

            $gross += $GrossPay['allow'];
            $deduc += $GrossPay['deduc'];
            $row++;
        }

        // Add Gross total and apply bold style
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('G' . $row, number_format($gross));
        $sheet->setCellValue('H' . $row, number_format($deduc));
        $sheet->setCellValue('I' . $row, number_format($gross-$deduc));
        $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);

        // Generate Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'GrossPay'.'_' . $periodDesc . '.xlsx';

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
