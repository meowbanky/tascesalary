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

if (isset($_GET['payperiod']) && isset($_GET['bank'])) {
    $periodRaw = $_GET['payperiod'];
    $period = App::normalizePeriodId($periodRaw);
    $bank = $_GET['bank'];
    $email = isset($_GET['email']) ? urldecode($_GET['email']) : '';

    // Validate inputs
    if ($period === null || $bank === '') {
        error_log('Invalid inputs: payperiod=' . $period . ', bank=' . $bank);
        die('Error: Pay period and bank are required.');
    }

    // Get period description
    $periodDescs = $App->getPeriodDescription($period);
    if (!$periodDescs) {
        error_log('Invalid period description for period: ' . $period);
        die('Error: Invalid or missing period description.');
    }
    $periodDesc = $periodDescs['period'];

    // Get bank name with backward compatibility (in case getBankName helper is absent)
    if ($bank == -1) {
        $bankName = 'All Banks';
    } else {
        if (method_exists($App, 'getBankName')) {
            $bankRow = $App->getBankName($bank);
        } else {
            $bankRow = $App->getBanksDetails($bank);
        }
        $bankName = $bankRow['BNAME'] ?? 'Unknown Bank';
    }

    if ($bank != -1 && $bankName === 'Unknown Bank') {
        error_log('Invalid bank ID: ' . $bank);
        die('Error: Invalid bank selection.');
    }

    // Get business information
    $businessInfo = $App->getBusinessName();
    if (!$businessInfo) {
        error_log('Failed to retrieve business information');
        die('Error: Unable to retrieve business information.');
    }
    $businessName = str_replace(',', ', ', $businessInfo['business_name']);

    // Get net to bank data
    $grossPays = $App->getBankSummary($period, $bank);
    if (!$grossPays) {
        error_log('No data for period: ' . $period . ', bank: ' . $bank);
        die('Error: No data available for the selected period and bank.');
    }

    // Initialize PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Net to Bank Report');

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(20); // S/N
    $sheet->getColumnDimension('B')->setWidth(30); // NAME
    $sheet->getColumnDimension('C')->setWidth(15); // STATUS
    $sheet->getColumnDimension('D')->setWidth(15); // AMOUNT
    $sheet->getColumnDimension('E')->setWidth(15); // PAYMENT DATE
    $sheet->getColumnDimension('F')->setWidth(20); // BENEFICIARY CODE
    $sheet->getColumnDimension('G')->setWidth(20); // ACCOUNT
    $sheet->getColumnDimension('H')->setWidth(15); // BANK CODE
    $sheet->getColumnDimension('I')->setWidth(15); // DEBIT ACCOUNT
    $sheet->getColumnDimension('J')->setWidth(20); // BANK

    // Header
    $sheet->mergeCells('A1:J1');
    $sheet->setCellValue('A1', $businessName);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:J2');
    $sheet->setCellValue('A2', 'NET TO BANK REPORT');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:J3');
    $sheet->setCellValue('A3', 'Period: ' . $periodDesc);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A4:J4');
    $sheet->setCellValue('A4', 'Bank: ' . $bankName);
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Table header
    $headerRow = 5;
    $headers = ['S/N', 'NAME', 'STATUS', 'AMOUNT', 'PAYMENT DATE', 'BENEFICIARY CODE', 'ACCOUNT', 'BANK CODE', 'DEBIT ACCOUNT', 'BANK'];
    $sheet->fromArray($headers, NULL, 'A' . $headerRow);
    $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
    $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Table data
    $row = $headerRow + 1;
    $totalAmount = 0.0;
    $sn = 1;
    foreach ($grossPays as $grossPay) {
        $net = is_numeric($grossPay['allow']) && is_numeric($grossPay['deduc']) ? floatval($grossPay['allow'] - $grossPay['deduc']) : 0.0;
        $padded = str_pad($grossPay['staff_id'] ?? '', 3, "0", STR_PAD_LEFT);
        $sm_padd = str_pad($sn, 3, "0", STR_PAD_LEFT);
        $sn_des = 'TASCE_' . $periodDesc . '_SAL_' . $sm_padd;

        $sheet->setCellValue('A' . $row, $sn_des);
        $sheet->setCellValue('B' . $row, $grossPay['NAME'] ?? '');
        $sheet->setCellValue('C' . $row, $grossPay['STATUSCD'] ?? '');
        $sheet->setCellValue('D' . $row, $net);
        $sheet->setCellValue('E' . $row, date('d/m/Y'));
        $sheet->setCellValue('F' . $row, 'TASCE ' . $padded);
        $sheet->setCellValue('G' . $row, $grossPay['acctno'] ?? '');
        $sheet->setCellValue('H' . $row, $grossPay['bankcode'] ?? '');
        $sheet->setCellValue('I' . $row, '1229191715');
        $sheet->setCellValue('J' . $row, $grossPay['bankname'] ?? '');

        // Apply text wrapping
        $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('J' . $row)->getAlignment()->setWrapText(true);

        // Apply number formatting
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $totalAmount += $net;
        $sn++;
        $row++;
    }

    // Totals
    $sheet->setCellValue('A' . $row, 'Total');
    $sheet->mergeCells('A' . $row . ':C' . $row);
    $sheet->setCellValue('D' . $row, $totalAmount);
    $sheet->mergeCells('E' . $row . ':J' . $row);
    $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Generate Excel file
    $filename = 'Net2Bank_Report_' . str_replace(' ', '_', $periodDesc) . '.xlsx';
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
            $mailer->Subject = 'Net to Bank Report - ' . $periodDesc;
            $mailer->Body = 'Dear User,<br><br>Please find attached the Net to Bank Report for the period ' . $periodDesc . '.<br><br>Best regards,<br>TASCE Salary Team';
            $mailer->AltBody = 'Dear User,\n\nPlease find attached the Net to Bank Report for the period ' . $periodDesc . '.\n\nBest regards,\nTASCE Salary Team';

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
    echo 'Invalid request. Please provide a valid period and bank.';
}
?>