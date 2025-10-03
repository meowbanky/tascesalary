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

if (isset($_GET['payperiod'])) {
    $period = $_GET['payperiod'];
    $email = isset($_GET['email']) ? urldecode($_GET['email']) : '';

    // Validate inputs
    if (empty($period)) {
        error_log('Invalid input: payperiod=' . $period);
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

    // Get gross pay data
    $grossPays = $App->getBankSummary($period);
    if (!$grossPays) {
        error_log('No data for period: ' . $period);
        die('Error: No data available for the selected period.');
    }

    // Initialize PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Gross Pay Report');

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(15); // Staff No
    $sheet->getColumnDimension('B')->setWidth(30); // Name
    $sheet->getColumnDimension('C')->setWidth(25); // Salary Structure
    $sheet->getColumnDimension('D')->setWidth(25); // Department
    $sheet->getColumnDimension('E')->setWidth(15); // Grade/Step
    $sheet->getColumnDimension('F')->setWidth(20); // Account No
    $sheet->getColumnDimension('G')->setWidth(20); // Bank
    $sheet->getColumnDimension('H')->setWidth(15); // Gross
    $sheet->getColumnDimension('I')->setWidth(15); // Deduction
    $sheet->getColumnDimension('J')->setWidth(15); // Net

    // Header
    $sheet->mergeCells('A1:J1');
    $sheet->setCellValue('A1', $businessName);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:J2');
    $sheet->setCellValue('A2', 'GROSS PAY REPORT');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:J3');
    $sheet->setCellValue('A3', 'Period: ' . $periodDesc);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Table header
    $headerRow = 4;
    $headers = ['Staff No', 'Name', 'Salary Structure', 'Department', 'Grade/Step', 'Account No', 'Bank', 'Gross', 'Deduction', 'Net'];
    $sheet->fromArray($headers, NULL, 'A' . $headerRow);
    $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
    $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Table data
    $row = $headerRow + 1;
    $totalGross = $totalDeduc = $totalNet = 0.0;
    foreach ($grossPays as $grossPay) {
        $allow = is_numeric($grossPay['allow']) ? floatval($grossPay['allow']) : 0.0;
        $deduc = is_numeric($grossPay['deduc']) ? floatval($grossPay['deduc']) : 0.0;
        $net = $allow - $deduc;

        $sheet->setCellValue('A' . $row, $grossPay['OGNO'] ?? '');
        $sheet->setCellValue('B' . $row, $grossPay['NAME'] ?? '');
        $sheet->setCellValue('C' . $row, $grossPay['SalaryType'] ?? '');
        $sheet->setCellValue('D' . $row, $grossPay['dept'] ?? '');
        $sheet->setCellValue('E' . $row, ($grossPay['grade'] ?? '') . '/' . ($grossPay['step'] ?? ''));
        $sheet->setCellValue('F' . $row, $grossPay['acctno'] ?? '');
        $sheet->setCellValue('G' . $row, $grossPay['bankname'] ?? '');
        $sheet->setCellValue('H' . $row, $allow);
        $sheet->setCellValue('I' . $row, $deduc);
        $sheet->setCellValue('J' . $row, $net);

        // Apply text wrapping
        $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);

        // Apply number formatting
        $sheet->getStyle('H' . $row . ':J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $totalGross += $allow;
        $totalDeduc += $deduc;
        $totalNet += $net;
        $row++;
    }

    // Totals
    $sheet->setCellValue('A' . $row, 'Total');
    $sheet->mergeCells('A' . $row . ':G' . $row);
    $sheet->setCellValue('H' . $row, $totalGross);
    $sheet->setCellValue('I' . $row, $totalDeduc);
    $sheet->setCellValue('J' . $row, $totalNet);
    $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
    $sheet->getStyle('H' . $row . ':J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Generate Excel file
    $filename = 'GrossPay_Report_' . str_replace(' ', '_', $periodDesc) . '.xlsx';
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
            $mailer->Subject = 'Gross Pay Report - ' . $periodDesc;
            $mailer->Body = 'Dear User,<br><br>Please find attached the Gross Pay Report for the period ' . $periodDesc . '.<br><br>Best regards,<br>TASCE Salary Team';
            $mailer->AltBody = 'Dear User,\n\nPlease find attached the Gross Pay Report for the period ' . $periodDesc . '.\n\nBest regards,\nTASCE Salary Team';

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
    echo 'Invalid request. Please provide a valid period.';
}
?>