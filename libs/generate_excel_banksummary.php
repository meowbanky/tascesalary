<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../libs/App.php';

$App = new App();
$App->checkAuthentication();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

if(isset($_GET['period'])) {
    $period = $_GET['period'];

    $periodDescs = $App->getPeriodDescription($period);
    $periodDesc = $periodDescs['period'];

    $banksummary = $App->getBankSummaryGroupBy($period);

    if($banksummary) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = ['Bank Name', 'No. of Staff', 'Net Pay'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Apply bold style to header row
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $gross = 0;
        $count = 0;

        // Fill allowance data rows
        $row = 2;
        foreach ($banksummary as $summary) {
            $sheet->setCellValue('A' . $row, $summary['BNAME']);
            $sheet->setCellValue('B' . $row, $summary['staff_count']);
            $sheet->setCellValue('C' . $row, $summary['net']);
            $gross += $summary['net'];
            $count += $summary['staff_count'];
            $row++;
        }

        // Add Gross total and apply bold style
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('B' . $row, $count);
        $sheet->setCellValue('C' . $row, number_format($gross));

        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $row++;


        // Generate Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'bankSummary_' . $periodDesc . '.xlsx';

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
