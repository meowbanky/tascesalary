<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once 'App.php';

$App = new App();
$App->checkAuthentication();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

if (isset($_GET['payperiod'])) {
    $period = $_GET['payperiod'];
    $summary = $App->getDeductionSummaryWithPayees($period);
    $periodDesc = $App->getPeriodDescription($period);
    $periodText = $periodDesc['period'] ?? '';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Deduction Summary');

    // Header
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'SIKIRU ADETONA COLLEGE OF EDUCATION SCIENCE AND TECHNOLOGY, OMU-AJOSE');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:E2');
    $formattedPeriod = str_replace('-', '. ', strtoupper($periodText));
    $sheet->setCellValue('A2', 'DEDUCTIONS FOR THE MONTH OF ' . $formattedPeriod);
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Column Headings
    $sheet->setCellValue('A4', 'S/N');
    $sheet->setCellValue('B4', 'PAYEE');
    $sheet->setCellValue('C4', 'BANK NAME');
    $sheet->setCellValue('D4', 'ACCOUNT NO');
    $sheet->setCellValue('E4', 'AMOUNT');

    $sheet->getStyle('A4:E4')->getFont()->setBold(true);
    $sheet->getStyle('A4:E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A4:E4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Data
    $row = 5;
    $sn = 1;
    $total = 0;
    if ($summary) {
        foreach ($summary as $item) {
            // Only include entries with bank details as requested
            if (empty($item['bank_name']) || empty($item['account_no'])) {
                continue;
            }
            
            $sheet->setCellValue('A' . $row, $sn++);
            $sheet->setCellValue('B' . $row, $item['payee_name']);
            $sheet->setCellValue('C' . $row, $item['bank_name']);
            $sheet->setCellValue('D' . $row, $item['account_no']);
            $sheet->setCellValue('E' . $row, $item['amount']);

            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $total += $item['amount'];
            $row++;
        }
    }

    // Total Row
    $sheet->setCellValue('A' . $row, 'TOTAL');
    $sheet->mergeCells('A' . $row . ':D' . $row);
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('E' . $row, $total);
    $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $fileName = 'Deduction_Summary_' . str_replace(' ', '_', $periodText) . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit();
}
?>
