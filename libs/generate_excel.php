<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$employees = $App->getEmployeeDetails(); // Assuming you have a method to get all employees

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header row
$headers = ['Staff ID', 'Name','Employment Type', 'Gender', 'Email','Date of Birth', 'Department', 'PFA', 'PFA PIN.', 'Bank', 'Account No.', 'SALARY TYPE', 'Grade', 'Step', 'Status'];
$sheet->fromArray($headers, NULL, 'A1');

// Fill data rows
$row = 2;
foreach ($employees as $employee) {
    $sheet->setCellValue('A' . $row, $employee['OGNO']);
    $sheet->setCellValue('B' . $row, $employee['NAME']);
    $sheet->setCellValue('C' . $row, $employee['employment_type']);
    $sheet->setCellValue('D' . $row, $employee['GENDER']);
    $sheet->setCellValue('E' . $row, $employee['EMAIL']);
    $sheet->setCellValue('F' . $row, $employee['DOB']);
    $sheet->setCellValue('G' . $row, $employee['dept']);
    $sheet->setCellValue('H' . $row, $employee['PFANAME']);
    $sheet->setCellValue('I' . $row, $employee['PFAACCTNO']);
    $sheet->setCellValue('J' . $row, $employee['BNAME']);
    $sheet->setCellValue('K' . $row, $employee['ACCTNO']);
    $sheet->setCellValue('L' . $row, $employee['SalaryType']);
    $sheet->setCellValue('M' . $row, $employee['GRADE']);
    $sheet->setCellValue('N' . $row, $employee['STEP']);
    $sheet->setCellValue('O' . $row, $employee['STATUS']);
    $row++;
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
