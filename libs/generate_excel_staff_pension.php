<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$App = new App();
$App->checkAuthentication();

if (!isset($_GET['staff_id'], $_GET['period_from'], $_GET['period_to'])) {
    http_response_code(400);
    exit('Invalid request.');
}

$staffId = $_GET['staff_id'];
$periodFrom = $_GET['period_from'];
$periodTo = $_GET['period_to'];

if (!ctype_digit((string)$staffId) || !ctype_digit((string)$periodFrom) || !ctype_digit((string)$periodTo)) {
    http_response_code(400);
    exit('Invalid parameters.');
}

$staffId = (int)$staffId;
$periodFrom = (int)$periodFrom;
$periodTo = (int)$periodTo;

if ($periodFrom > $periodTo) {
    http_response_code(400);
    exit('Invalid period range.');
}

$profile = $App->getStaffProfile($staffId);
$history = $App->getStaffPensionHistory($staffId, $periodFrom, $periodTo);

if (!$profile) {
    http_response_code(404);
    exit('Staff not found.');
}

if (!$history) {
    http_response_code(404);
    exit('No pension data for the selected range.');
}

$periodFromDesc = $App->getPeriodDescription($periodFrom);
$periodToDesc = $App->getPeriodDescription($periodTo);

$periodRangeLabel = sprintf(
    '%s to %s',
    $periodFromDesc['period'] ?? $periodFrom,
    $periodToDesc['period'] ?? $periodTo
);

$businessInfo = $App->getBusinessName();
$businessName = $businessInfo['business_name'] ?? 'Pension Report';
$businessName = str_replace(',', ', ', $businessName);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Staff Pension History');

$sheet->mergeCells('A1:C1');
$sheet->setCellValue('A1', $businessName);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A2:C2');
$sheet->setCellValue('A2', 'Staff Pension History');
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A3:C3');
$sheet->setCellValue('A3', 'Period Range: ' . $periodRangeLabel);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A5', 'Staff Name:');
$sheet->setCellValue('B5', $profile['NAME'] ?? '');
$sheet->setCellValue('A6', 'Staff No:');
$sheet->setCellValue('B6', $profile['staff_id'] ?? '');
$sheet->setCellValue('A7', 'OGNO:');
$sheet->setCellValue('B7', $profile['OGNO'] ?? '');
$sheet->setCellValue('A8', 'PFA:');
$sheet->setCellValue('B8', $profile['PFANAME'] ?? '');
$sheet->setCellValue('A9', 'PFA Code:');
$sheet->setCellValue('B9', $profile['PFACODE'] ?? '');
$sheet->setCellValue('A10', 'PFA PIN:');
$sheet->setCellValue('B10', $profile['PFAACCTNO'] ?? '');

$sheet->getStyle('A5:A10')->getFont()->setBold(true);

$startRow = 12;
$headers = ['#', 'Period', 'Amount'];
$sheet->fromArray($headers, null, 'A' . $startRow);
$sheet->getStyle('A' . $startRow . ':C' . $startRow)->getFont()->setBold(true);
$sheet->getStyle('A' . $startRow . ':C' . $startRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A' . $startRow . ':C' . $startRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('A' . $startRow . ':C' . $startRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');

$row = $startRow + 1;
$sn = 1;
$total = 0;

foreach ($history as $item) {
    $amount = (float)($item['amount'] ?? 0);
    $total += $amount;

    $sheet->setCellValue('A' . $row, $sn++);
    $sheet->setCellValue('B' . $row, $item['period_name'] ?? $item['period']);
    $sheet->setCellValue('C' . $row, $amount);

    $sheet->getStyle('A' . $row . ':C' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $row++;
}

$sheet->setCellValue('A' . $row, 'Total');
$sheet->mergeCells('A' . $row . ':B' . $row);
$sheet->setCellValue('C' . $row, $total);
$sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
$sheet->getStyle('A' . $row . ':C' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(25);
$sheet->getColumnDimension('C')->setWidth(18);

$filename = sprintf(
    'Staff_Pension_%s_%s.xlsx',
    preg_replace('/\s+/', '_', $profile['staff_id'] ?? 'staff'),
    date('Ymd_His')
);
$filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
$writer = new Xlsx($spreadsheet);
$writer->save($filePath);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
readfile($filePath);
unlink($filePath);
exit;