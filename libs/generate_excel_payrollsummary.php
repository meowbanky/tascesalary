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

if (isset($_GET['period'])) {
    $period = $_GET['period'];
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

    // Get payroll summary data
    $allowanceSummarys = $App->getReportSummary($period, 'allow');
    $deductionSummarys = $App->getReportSummary($period, 'deduc');
    if (!$allowanceSummarys && !$deductionSummarys) {
        error_log('No payroll summary data for period: ' . $period);
        die('Error: No payroll summary data available for the selected period.');
    }

    // Initialize PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Payroll Summary');

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(50); // Code Description
    $sheet->getColumnDimension('B')->setWidth(30); // Amount

    // Header
    $sheet->mergeCells('A1:B1');
    $sheet->setCellValue('A1', $businessName);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:B2');
    $sheet->setCellValue('A2', 'PAYROLL SUMMARY');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:B3');
    $sheet->setCellValue('A3', 'Period: ' . $periodDesc);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Table header
    $headerRow = 4;
    $headers = ['Code Description', 'Amount'];
    $sheet->fromArray($headers, NULL, 'A' . $headerRow);
    $sheet->getStyle('A' . $headerRow . ':B' . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $headerRow . ':B' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
    $sheet->getStyle('A' . $headerRow . ':B' . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    $gross = 0.0;
    $deduction = 0.0;
    $row = $headerRow + 1;

    // Allowances section
    $sheet->setCellValue('A' . $row, 'Allowances');
    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
    $sheet->mergeCells('A' . $row . ':B' . $row);
    $row++;

    if ($allowanceSummarys) {
        foreach ($allowanceSummarys as $index => $allowanceSummary) {
            if (!isset($allowanceSummary['value']) || is_array($allowanceSummary['value']) || !is_numeric($allowanceSummary['value'])) {
                error_log('Invalid allowance value for ' . ($allowanceSummary['edDesc'] ?? 'unknown') . ': ' . json_encode($allowanceSummary['value'] ?? 'unset'));
                continue;
            }
            $value = floatval($allowanceSummary['value']);
            $sheet->setCellValue('A' . $row, $allowanceSummary['edDesc']);
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $gross += $value;
            $row++;
        }
    } else {
        $sheet->setCellValue('A' . $row, 'No allowances for this period.');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;
    }

    // Gross total
    $sheet->setCellValue('A' . $row, 'Gross Total');
    $sheet->setCellValue('B' . $row, $gross);
    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
    $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $row += 2; // Extra row for spacing

    // Deductions section
    $sheet->setCellValue('A' . $row, 'Deductions');
    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
    $sheet->mergeCells('A' . $row . ':B' . $row);
    $row++;

    if ($deductionSummarys) {
        foreach ($deductionSummarys as $index => $deductionSummary) {
            if (!isset($deductionSummary['value']) || is_array($deductionSummary['value']) || !is_numeric($deductionSummary['value'])) {
                error_log('Invalid deduction value for ' . ($deductionSummary['edDesc'] ?? 'unknown') . ': ' . json_encode($deductionSummary['value'] ?? 'unset'));
                continue;
            }
            $value = floatval($deductionSummary['value']);
            $sheet->setCellValue('A' . $row, $deductionSummary['edDesc']);
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $deduction += $value;
            $row++;
        }
    } else {
        $sheet->setCellValue('A' . $row, 'No deductions for this period.');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;
    }

    // Deduction total
    $sheet->setCellValue('A' . $row, 'Deduction Total');
    $sheet->setCellValue('B' . $row, $deduction);
    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
    $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $row += 2; // Extra row for spacing

    // Net total
    $sheet->setCellValue('A' . $row, 'Net Total');
    $sheet->setCellValue('B' . $row, $gross - $deduction);
    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Generate Excel file
    $filename = 'Payroll_Summary_' . str_replace(' ', '_', $periodDesc) . '.xlsx';
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
            $mailer->Subject = 'Payroll Summary Report - ' . $periodDesc;
            $mailer->Body = 'Dear User,<br><br>Please find attached the Payroll Summary report for the period ' . $periodDesc . '.<br><br>Best regards,<br>TASCE Salary Team';
            $mailer->AltBody = 'Dear User,\n\nPlease find attached the Payroll Summary report for the period ' . $periodDesc . '.\n\nBest regards,\nTASCE Salary Team';

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