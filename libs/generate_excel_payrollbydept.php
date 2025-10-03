<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$App = new App();
$App->checkAuthentication();

if (isset($_GET['payperiod']) && isset($_GET['dept'])) {
    $period = $_GET['payperiod'];
    $deptcd = $_GET['dept'] === '' ? null : $_GET['dept'];
    $email = isset($_GET['email']) ? urldecode($_GET['email']) : '';

    // Validate inputs
    if (empty($period)) {
        error_log('Invalid input: period is required');
        die('Error: Pay period is required.');
    }

    // Get period description
    $periodDescs = $App->getPeriodDescription($period);
    if (!$periodDescs) {
        error_log('Invalid period description for period: ' . $period);
        die('Error: Invalid or missing period description.');
    }
    $periodDesc = $periodDescs['period'];

    // Get business information
    $businessInfo = $App->getBusinessName();
    if (!$businessInfo) {
        error_log('Failed to retrieve business information');
        die('Error: Unable to retrieve business information.');
    }
    $businessName = str_replace(',', ', ', $businessInfo['business_name']);

    // Get department payroll data
    $payrollData = $App->getBankSummaryGroupBy($period, 'master_staff.DEPTCD', $deptcd);
    if (!$payrollData) {
        error_log('No payroll data for period: ' . $period . ', deptcd: ' . ($deptcd ?? 'null'));
        die('Error: No payroll data available for the selected period and department.');
    }
    error_log('Payroll Data for Excel: ' . json_encode($payrollData));

    // Initialize PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Payroll Report');

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(10); // S/N
    $sheet->getColumnDimension('B')->setWidth(30); // Department Name or Name
    if ($deptcd) {
        $sheet->getColumnDimension('C')->setWidth(20); // Salary Structure
        $sheet->getColumnDimension('D')->setWidth(10); // Grade
        $sheet->getColumnDimension('E')->setWidth(10); // Step
        $sheet->getColumnDimension('F')->setWidth(15); // No of Staff
        $sheet->getColumnDimension('G')->setWidth(20); // Total Allowance
        $sheet->getColumnDimension('H')->setWidth(20); // Total Deduction
        $sheet->getColumnDimension('I')->setWidth(20); // Net
    } else {
        $sheet->getColumnDimension('C')->setWidth(15); // No of Staff
        $sheet->getColumnDimension('D')->setWidth(20); // Total Allowance
        $sheet->getColumnDimension('E')->setWidth(20); // Total Deduction
        $sheet->getColumnDimension('F')->setWidth(20); // Net
    }

    // Header
    $sheet->mergeCells('A1:' . ($deptcd ? 'I1' : 'F1'));
    $sheet->setCellValue('A1', $businessName);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:' . ($deptcd ? 'I2' : 'F2'));
    $sheet->setCellValue('A2', 'DEPARTMENT PAYROLL REPORT');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:' . ($deptcd ? 'I3' : 'F3'));
    $sheet->setCellValue('A3', 'Period: ' . $periodDesc);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    if ($deptcd) {
        $deptDetails = $App->getDeptDetails($deptcd);
        $deptName = $deptDetails[0]['dept'] ?? 'Unknown';
        $sheet->mergeCells('A4:' . ($deptcd ? 'I4' : 'F4'));
        $sheet->setCellValue('A4', 'Department: ' . $deptName);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $headerRow = 5;
    } else {
        $headerRow = 4;
    }

    // Table header
    $sheet->setCellValue('A' . $headerRow, 'S/N');
    $sheet->setCellValue('B' . $headerRow, $deptcd ? 'Name' : 'Department Name');
    if ($deptcd) {
        $sheet->setCellValue('C' . $headerRow, 'Salary Structure');
        $sheet->setCellValue('D' . $headerRow, 'Grade');
        $sheet->setCellValue('E' . $headerRow, 'Step');
        $sheet->setCellValue('F' . $headerRow, 'No of Staff');
        $sheet->setCellValue('G' . $headerRow, 'Total Allowance');
        $sheet->setCellValue('H' . $headerRow, 'Total Deduction');
        $sheet->setCellValue('I' . $headerRow, 'Net');
    } else {
        $sheet->setCellValue('C' . $headerRow, 'No of Staff');
        $sheet->setCellValue('D' . $headerRow, 'Total Allowance');
        $sheet->setCellValue('E' . $headerRow, 'Total Deduction');
        $sheet->setCellValue('F' . $headerRow, 'Net');
    }

    $sheet->getStyle('A' . $headerRow . ':' . ($deptcd ? 'I' : 'F') . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $headerRow . ':' . ($deptcd ? 'I' : 'F') . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
    $sheet->getStyle('A' . $headerRow . ':' . ($deptcd ? 'I' : 'F') . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Table data
    $row = $headerRow + 1;
    $sn = 1;
    $totalAllow = $totalDeduc = $totalNet = $totalStaff = 0.0;
    foreach ($payrollData as $data) {
        $allow = is_numeric($data['allow']) ? floatval($data['allow']) : 0.0;
        $deduc = is_numeric($data['deduc']) ? floatval($data['deduc']) : 0.0;
        $net = is_numeric($data['net']) ? floatval($data['net']) : 0.0;
        $staff_count = is_numeric($data['staff_count']) ? intval($data['staff_count']) : 0;

        $sheet->setCellValue('A' . $row, $sn);
        $sheet->setCellValue('B' . $row, $deptcd ? ($data['NAME'] ?? '') : ($data['dept'] ?? ''));
        if ($deptcd) {
            $sheet->setCellValue('C' . $row, $data['SalaryType'] ?? '');
            $sheet->setCellValue('D' . $row, intval($data['grade'] ?? 0));
            $sheet->setCellValue('E' . $row, intval($data['step'] ?? 0));
            $sheet->setCellValue('F' . $row, $staff_count);
            $sheet->setCellValue('G' . $row, $allow);
            $sheet->setCellValue('H' . $row, $deduc);
            $sheet->setCellValue('I' . $row, $net);
        } else {
            $sheet->setCellValue('C' . $row, $staff_count);
            $sheet->setCellValue('D' . $row, $allow);
            $sheet->setCellValue('E' . $row, $deduc);
            $sheet->setCellValue('F' . $row, $net);
        }

        // Apply text wrapping
        $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        if ($deptcd) {
            $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
        }

        // Apply number formatting
        if ($deptcd) {
            $sheet->getStyle('G' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        } else {
            $sheet->getStyle('D' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $sheet->getStyle('A' . $row . ':' . ($deptcd ? 'I' : 'F') . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $totalAllow += $allow;
        $totalDeduc += $deduc;
        $totalNet += $net;
        $totalStaff += $staff_count;
        $row++;
        $sn++;
    }

    // Totals
    $sheet->setCellValue('A' . $row, 'Total');
    $sheet->mergeCells('A' . $row . ':' . ($deptcd ? 'E' : 'B') . $row);
    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
    if ($deptcd) {
        $sheet->setCellValue('F' . $row, $totalStaff);
        $sheet->setCellValue('G' . $row, $totalAllow);
        $sheet->setCellValue('H' . $row, $totalDeduc);
        $sheet->setCellValue('I' . $row, $totalNet);
        $sheet->getStyle('G' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    } else {
        $sheet->setCellValue('C' . $row, $totalStaff);
        $sheet->setCellValue('D' . $row, $totalAllow);
        $sheet->setCellValue('E' . $row, $totalDeduc);
        $sheet->setCellValue('F' . $row, $totalNet);
        $sheet->getStyle('D' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    }
    $sheet->getStyle('A' . $row . ':' . ($deptcd ? 'I' : 'F') . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('A' . $row . ':' . ($deptcd ? 'I' : 'F') . $row)->getFont()->setBold(true);

    // Generate Excel file
    $filename = 'Dept_Payroll_Report_' . str_replace(' ', '_', $periodDesc) . '.xlsx';
    $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    if ($email) {
        // Send email with PHPMailer
        $mailer = new PHPMailer(true);
        try {
            // Server settings
            $mailer->SMTPDebug = SMTPDEBUG;
            $mailer->isSMTP();
            $mailer->Host = HOST_MAIL;
            $mailer->SMTPAuth = true;
            $mailer->Username = USERNAME;
            $mailer->Password = PASSWORD;
            $mailer->SMTPSecure = SMTPSECURE;
            $mailer->Port = PORT;

            // Recipients
            $mailer->setFrom(USERNAME, SENDERNAME.' Salary Report');
            $mailer->addAddress($email);
            $mailer->addReplyTo(USERNAME, SENDERNAME.' Salary Report');

            // Attachments
            $mailer->addAttachment($filePath, $filename);

            // Content
            $mailer->isHTML(true);
            $mailer->Subject = 'Department Payroll Report - ' . $periodDesc;
            $mailer->Body = 'Dear User,<br><br>Please find attached the Department Payroll Report for the period ' . $periodDesc . '.<br><br>Best regards,<br>TASCE Salary Team';
            $mailer->AltBody = 'Dear User,\n\nPlease find attached the Department Payroll Report for the period ' . $periodDesc . '.\n\nBest regards,\nTASCE Salary Team';

            $mailer->send();
            unlink($filePath);
            echo 'The report has been sent to ' . htmlspecialchars($email) . '.';
            exit();
        } catch (Exception $e) {
            unlink($filePath);
            error_log('Mailer Error: ' . $mailer->ErrorInfo);
            die('Error: Could not send email. Mailer Error: ' . $mailer->ErrorInfo);
        }
    } else {
        // Trigger download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        readfile($filePath);
        unlink($filePath);
        exit();
    }
} else {
    echo 'Invalid request. Please provide a valid period and department.';
}
?>