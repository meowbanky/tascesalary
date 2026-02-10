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

if (isset($_GET['payperiod']) && isset($_GET['pfa'])) {
    $periodRaw = $_GET['payperiod'];
    $period = App::normalizePeriodId($periodRaw);
    $pfa = $_GET['pfa'];
    $email = isset($_GET['email']) ? urldecode($_GET['email']) : '';

    // Validate inputs
    if ($period === null || $pfa === '') {
        error_log('Invalid inputs: payperiod=' . $period . ', pfa=' . $pfa);
        die('Error: Pay period and PFA are required.');
    }

    // Get period description
    $periodDescs = $App->getPeriodDescription($period);
    if (!$periodDescs) {
        error_log('Invalid period description for period: ' . $period);
        die('Error: Invalid or missing period description.');
    }
    $periodDesc = $periodDescs['period'];

    // Get PFA name
    $pfaName = ($pfa == -1) ? 'All PFAs' : $App->selectDrop("SELECT PFANAME FROM tbl_pfa WHERE PFACODE = :pfa", ['pfa' => $pfa])[0]['PFANAME'] ?? 'Unknown PFA';
    if ($pfa != -1 && $pfaName == 'Unknown PFA') {
        error_log('Invalid PFA code: ' . $pfa);
        die('Error: Invalid PFA selection.');
    }

    // Get business information
    $businessInfo = $App->getBusinessName();
    if (!$businessInfo) {
        error_log('Failed to retrieve business information');
        die('Error: Unable to retrieve business information.');
    }
    $businessName = str_replace(',', ', ', $businessInfo['business_name']);

    // Get pension data
    $getPensions = $App->getPfa($period, $pfa);
    if (!$getPensions || !is_array($getPensions)) {
        error_log('No data or invalid data for period: ' . $period . ', pfa: ' . $pfa);
        die('Error: No data available for the selected period and PFA.');
    }

    // Initialize PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Pension Report');

    // Set column widths
    if ($pfa != -1) {
        $sheet->getColumnDimension('A')->setWidth(15); // S/N
        $sheet->getColumnDimension('B')->setWidth(20); // Staff No
        $sheet->getColumnDimension('C')->setWidth(45); // Name
        $sheet->getColumnDimension('D')->setWidth(30); // PFA PIN
        $sheet->getColumnDimension('E')->setWidth(35); // PFA
        $sheet->getColumnDimension('F')->setWidth(30); // AMOUNT
    } else {
        $sheet->getColumnDimension('A')->setWidth(15); // S/N
        $sheet->getColumnDimension('B')->setWidth(130); // PFA
        $sheet->getColumnDimension('C')->setWidth(30); // AMOUNT
    }

    // Header
    $lastColumn = ($pfa != -1) ? 'F' : 'C';
    $sheet->mergeCells('A1:' . $lastColumn . '1');
    $sheet->setCellValue('A1', $businessName);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:' . $lastColumn . '2');
    $sheet->setCellValue('A2', 'PENSION REPORT');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:' . $lastColumn . '3');
    $sheet->setCellValue('A3', 'Period: ' . $periodDesc);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A4:' . $lastColumn . '4');
    $sheet->setCellValue('A4', 'PFA: ' . $pfaName);
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Table header
    $headerRow = 5;
    if ($pfa != -1) {
        $headers = ['S/N', 'Staff No', 'Name', 'PFA PIN', 'PFA', 'AMOUNT'];
        $sheet->fromArray($headers, NULL, 'A' . $headerRow);
    } else {
        $headers = ['S/N', 'PFA', 'AMOUNT'];
        $sheet->fromArray($headers, NULL, 'A' . $headerRow);
    }
    $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
    $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Table data
    $row = $headerRow + 1;
    $grossPension = 0.0;
    $sn = 1;
    foreach ($getPensions as $getPension) {
        if ($pfa != -1) {
            $sheet->setCellValue('A' . $row, $sn);
            $sheet->setCellValue('B' . $row, $getPension['OGNO'] ?? '');
            $sheet->setCellValue('C' . $row, $getPension['NAME'] ?? '');
            $sheet->setCellValue('D' . $row, $getPension['PFAACCTNO'] ?? '');
            $sheet->setCellValue('E' . $row, $getPension['PFANAME'] ?? '');
            $sheet->setCellValue('F' . $row, $getPension['deduc']);
            // Apply text wrapping
            $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            // Apply number formatting
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        } else {
            $sheet->setCellValue('A' . $row, $sn);
            $sheet->setCellValue('B' . $row, $getPension['PFANAME'] ?? '');
            $sheet->setCellValue('C' . $row, $getPension['deduc']);
            // Apply text wrapping
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            // Apply number formatting
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':C' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        $grossPension += floatval($getPension['deduc']);
        $sn++;
        $row++;
    }

    // Totals
    if ($pfa != -1) {
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('F' . $row, $grossPension);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    } else {
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('C' . $row, $grossPension);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    // Generate Excel file
    $filename = 'Pension_Report_' . str_replace(' ', '_', $periodDesc) . '.xlsx';
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
            $mailer->Subject = 'Pension Report - ' . $periodDesc;
            $mailer->Body = 'Dear User,<br><br>Please find attached the Pension Report for the period ' . $periodDesc . '.<br><br>Best regards,<br>TASCE Salary Team';
            $mailer->AltBody = 'Dear User,\n\nPlease find attached the Pension Report for the period ' . $periodDesc . '.\n\nBest regards,\nTASCE Salary Team';

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
    echo 'Invalid request. Please provide a valid period and PFA.';
}
?>