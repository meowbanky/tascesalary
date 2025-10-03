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

if (isset($_GET['payperiod']) && isset($_GET['allow_id']) && isset($_GET['type'])) {
    $period = $_GET['payperiod'];
    $allow_id = $_GET['allow_id'];
    $type = $_GET['type'];
    $email = isset($_GET['email']) ? urldecode($_GET['email']) : '';

    // Validate inputs
    if (empty($period) || empty($allow_id)) {
        error_log('Invalid inputs: payperiod=' . $period . ', allow_id=' . $allow_id);
        die('Error: Pay period and allowance/deduction are required.');
    }

    // Get period description
    $periodDescs = $App->getPeriodDescription($period);
    if (!$periodDescs) {
        error_log('Invalid period description for period: ' . $period);
        die('Error: Invalid or missing period description.');
    }
    $periodDesc = $periodDescs['period'];

    // Get allowance/deduction description
    $allowDescription = $App->getAllowanceDescription($allow_id);
    if (!$allowDescription) {
        error_log('Invalid allowance description for allow_id: ' . $allow_id);
        die('Error: Invalid or missing allowance/deduction description.');
    }
    $allowDescription = $allowDescription['ed'];
    $allow_deduc = $type == 1 ? 'Allowance' : 'Deduction';

    // Get business information
    $businessInfo = $App->getBusinessName();
    if (!$businessInfo) {
        error_log('Failed to retrieve business information');
        die('Error: Unable to retrieve business information.');
    }
    $businessName = str_replace(',', ', ', $businessInfo['business_name']);

    // Get deduction/allowance data
    $deductions = $App->getReportDeductionList($period, $type, $allow_id);
    if (!$deductions) {
        error_log('No data for period: ' . $period . ', allow_id: ' . $allow_id . ', type: ' . $type);
        die('Error: No data available for the selected period and allowance/deduction.');
    }

    // Initialize PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($allow_deduc . ' Report');

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(10); // S/N
    $sheet->getColumnDimension('B')->setWidth(15); // Staff No
    $sheet->getColumnDimension('C')->setWidth(30); // Name
    $sheet->getColumnDimension('D')->setWidth(15); // Amount

    // Header
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A1', $businessName);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:D2');
    $sheet->setCellValue('A2', strtoupper($allow_deduc . ' REPORT'));
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:D3');
    $sheet->setCellValue('A3', 'Period: ' . $periodDesc);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A4:D4');
    $sheet->setCellValue('A4', $allow_deduc . ': ' . $allowDescription);
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Table header
    $headerRow = 5;
    $sheet->setCellValue('A' . $headerRow, 'S/N');
    $sheet->setCellValue('B' . $headerRow, 'Staff No');
    $sheet->setCellValue('C' . $headerRow, 'Name');
    $sheet->setCellValue('D' . $headerRow, 'Amount');

    $sheet->getStyle('A' . $headerRow . ':D' . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $headerRow . ':D' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
    $sheet->getStyle('A' . $headerRow . ':D' . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Table data
    $row = $headerRow + 1;
    $sn = 1;
    $gross = 0.0;
    foreach ($deductions as $deduction) {
        $value = is_numeric($deduction['value']) ? floatval($deduction['value']) : 0.0;

        $sheet->setCellValue('A' . $row, $sn);
        $sheet->setCellValue('B' . $row, $deduction['OGNO'] ?? '');
        $sheet->setCellValue('C' . $row, $deduction['NAME'] ?? '');
        $sheet->setCellValue('D' . $row, $value);

        // Apply text wrapping
        $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);

        // Apply number formatting
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $gross += $value;
        $row++;
        $sn++;
    }

    // Totals
    $sheet->setCellValue('A' . $row, 'Total');
    $sheet->mergeCells('A' . $row . ':C' . $row);
    $sheet->setCellValue('D' . $row, $gross);
    $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Generate Excel file
    $filename = $allow_deduc . '_Report_' . str_replace(' ', '_', $periodDesc) . '.xlsx';
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
            $mailer->Subject = $allow_deduc . ' Report - ' . $periodDesc;
            $mailer->Body = 'Dear User,<br><br>Please find attached the ' . $allow_deduc . ' Report for the period ' . $periodDesc . '.<br><br>Best regards,<br>TASCE Salary Team';
            $mailer->AltBody = 'Dear User,\n\nPlease find attached the ' . $allow_deduc . ' Report for the period ' . $periodDesc . '.\n\nBest regards,\nTASCE Salary Team';

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
    echo 'Invalid request. Please provide a valid period, allowance/deduction, and type.';
}
?>