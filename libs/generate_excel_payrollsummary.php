<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

$App = new App();
$App->checkAuthentication();


if(isset($_GET['period'])) {
    $period = $_GET['period'];

    $periodDescs = $App->getPeriodDescription($period);
    $periodDesc = $periodDescs['period'];

    $allowanceSummarys = $App->getReportSummary($period, 'allow');
    $deductionSummarys = $App->getReportSummary($period, 'deduc');

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header row
    $headers = ['Code Description', 'Amount'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Apply bold style to header row
    $sheet->getStyle('A1:B1')->getFont()->setBold(true);

    $gross = 0;
    $deduction = 0;

    // Fill allowance data rows
    $row = 2;
    foreach ($allowanceSummarys as $allowanceSummary) {
        $sheet->setCellValue('A' . $row, $allowanceSummary['edDesc']);
        $sheet->setCellValue('B' . $row, number_format($allowanceSummary['value']));
        $gross += $allowanceSummary['value'];
        $row++;
    }

    // Add Gross total and apply bold style
    $sheet->setCellValue('A' . $row, 'Gross');
    $sheet->setCellValue('B' . $row, number_format($gross));
    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
    $row++;

    // Fill deduction data rows
    foreach ($deductionSummarys as $deductionSummary) {
        $sheet->setCellValue('A' . $row, $deductionSummary['edDesc']);
        $sheet->setCellValue('B' . $row, number_format($deductionSummary['value']));
        $deduction += $deductionSummary['value'];
        $row++;
    }

    // Add Deduction total and apply bold style
    $sheet->setCellValue('A' . $row, 'Deduction');
    $sheet->setCellValue('B' . $row, number_format($deduction));
    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
    $row++;

    // Add NET total and apply bold style
    $sheet->setCellValue('A' . $row, 'NET');
    $sheet->setCellValue('B' . $row, number_format($gross - $deduction));
    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);

    // Generate Excel file
    $writer = new Xlsx($spreadsheet);
    $filename = 'payrollSummary_'.$periodDesc.'.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit();
} else {
    echo 'Invalid request. Please provide a valid period.';
}
?>
