<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

$employees = $App->getEmployeeDetails(); // Fetch employee data

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Define business name and report name
$businessName = "Sikiru Adetona College of Education, Science and Technology, Omu-Ajose";
$reportName = "Employee List";

// Set business name in the first row, centered
$sheet->setCellValue('A1', $businessName);
$sheet->mergeCells('A1:P1'); // Merge across all 16 columns (A to P)
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

// Set report name in the second row, centered
$sheet->setCellValue('A2', $reportName);
$sheet->mergeCells('A2:P2'); // Merge across all 16 columns (A to P)
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

// Set column headers in the third row
$headers = ['Staff ID', 'Name', 'TIN', 'Employment Type', 'Gender', 'Email', 'Date of Birth', 'Department', 'PFA', 'PFA PIN.', 'Bank', 'Account No.', 'SALARY TYPE', 'Grade', 'Step', 'Status'];
$sheet->fromArray($headers, NULL, 'A3');

// Style the header row
$sheet->getStyle('A3:P3')->getFont()->setBold(true);
$sheet->getStyle('A3:P3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Fill data rows starting from row 4
$row = 4;
foreach ($employees as $employee) {
    $sheet->setCellValue('A' . $row, $employee['OGNO']);
    $sheet->setCellValue('B' . $row, $employee['NAME']);
    $sheet->setCellValue('C' . $row, $employee['TIN']);
    $sheet->setCellValue('D' . $row, $employee['employment_type']);
    $sheet->setCellValue('E' . $row, $employee['GENDER']);
    $sheet->setCellValue('F' . $row, $employee['EMAIL']);
    $sheet->setCellValue('G' . $row, $employee['DOB']);
    $sheet->setCellValue('H' . $row, $employee['dept']);
    $sheet->setCellValue('I' . $row, $employee['PFANAME']);
    $sheet->setCellValue('J' . $row, $employee['PFAACCTNO']);
    $sheet->setCellValue('K' . $row, $employee['BNAME']);
    $sheet->setCellValue('L' . $row, $employee['ACCTNO']);
    $sheet->setCellValue('M' . $row, $employee['SalaryType']);
    $sheet->setCellValue('N' . $row, $employee['GRADE']);
    $sheet->setCellValue('O' . $row, $employee['STEP']);
    $sheet->setCellValue('P' . $row, $employee['STATUS']);
    $row++;
}

// Auto-size columns for better readability
foreach (range('A', 'P') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Generate Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'employees.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit();
?>