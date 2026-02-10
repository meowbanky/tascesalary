<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Custom TCPDF class to override footer
class CustomTCPDF extends TCPDF {
    private $printedBy = '';
    private $currentDate = '';

    public function setCustomFooterData($printedBy, $currentDate) {
        $this->printedBy = $printedBy !== null ? (string)$printedBy : '';
        $this->currentDate = $currentDate !== null ? (string)$currentDate : '';
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(90, 10, 'Date Printed: ' . $this->currentDate, 0, 0, 'L');
        $this->Cell(90, 10, 'Printed By: ' . $this->printedBy, 0, 1, 'R');
    }
}

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
    $businessNameRaw = $businessInfo['business_name'] ?? '';
    $businessName = str_replace(',', ",\n", (string)$businessNameRaw);

    // Get logged-in user details
    $userDetails = $App->getUsersDetails($_SESSION['SESS_MEMBER_ID']);
    if (!$userDetails) {
        error_log('Failed to retrieve user details for user ID: ' . $_SESSION['SESS_MEMBER_ID']);
        die('Error: Unable to retrieve user details.');
    }
    $printedBy = $userDetails['NAME'] ?? '';

    // Get payroll summary data
    $allowanceSummarys = $App->getReportSummary($period, 'allow');
    $deductionSummarys = $App->getReportSummary($period, 'deduc');
    if (!$allowanceSummarys && !$deductionSummarys) {
        error_log('No payroll summary data for period: ' . $period);
        die('Error: No payroll summary data available for the selected period.');
    }

    // Initialize TCPDF
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($printedBy);
    $pdf->SetTitle('Payroll Summary - ' . $periodDesc);
    $pdf->SetSubject('Payroll Summary Report');

    // Enable footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // Set custom footer data
    $currentDate = date('Y-m-d H:i:s');
    $pdf->setCustomFooterData($printedBy, $currentDate);

    // Set margins
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Header with logos and institution name
    $logoLeft = '../assets/images/ogun_logo.png';
    $logoRight = '../assets/images/tasce_r_logo.png';
    if (file_exists($logoLeft) && file_exists($logoRight)) {
        $pdf->Image($logoLeft, 15, 10, 25, 25, 'PNG', '', 'T', false, 300, '', false, false, 0);
        $pdf->Image($logoRight, 170, 10, 25, 25, 'PNG', '', 'T', false, 300, '', false, false, 0);
    } else {
        error_log('Logo files missing: ' . $logoLeft . ' or ' . $logoRight);
    }

    // Institution name
    $pdf->SetY(10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->MultiCell(140, 15, $businessName, 0, 'C', false, 1, 35, null, true, 0, false, true, 15, 'M');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'PAYROLL SUMMARY', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Period: ' . $periodDesc, 0, 1, 'C');
    $pdf->Ln(5);

    // Table header
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(100, 8, 'Description', 1, 0, 'L', 1);
    $pdf->Cell(80, 8, 'Amount', 1, 1, 'R', 1);
    $pdf->SetFont('helvetica', '', 10);

    $gross = 0.0;
    $deduction = 0.0;

    // Allowance rows
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 6, 'Allowances', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    if ($allowanceSummarys) {
        foreach ($allowanceSummarys as $index => $allowance) {
            if (!isset($allowance['value']) || is_array($allowance['value']) || !is_numeric($allowance['value'])) {
                error_log('Invalid allowance value for ' . ($allowance['edDesc'] ?? 'unknown') . ': ' . json_encode($allowance['value'] ?? 'unset'));
                continue;
            }
            $value = floatval($allowance['value']);
            if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
                $pdf->AddPage();
                $pdf->SetFillColor(200, 200, 200);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(100, 8, 'Description', 1, 0, 'L', 1);
                $pdf->Cell(80, 8, 'Amount', 1, 1, 'R', 1);
                $pdf->SetFont('helvetica', '', 10);
            }
            $descLines = $pdf->getNumLines($allowance['edDesc'] ?? '', 100);
            $rowHeight = max(6, 6 * $descLines);
            $pdf->MultiCell(100, $rowHeight, $allowance['edDesc'] ?? '', 1, 'L', false, 0);
            $pdf->Cell(80, $rowHeight, number_format($value, 2), 1, 1, 'R');
            $gross += $value;
        }
    } else {
        $pdf->Cell(180, 6, 'No allowances for this period.', 1, 1, 'L');
    }

    // Gross total
    $pdf->SetFont('helvetica', 'B', 10);
    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
        $pdf->AddPage();
    }
    $pdf->Cell(100, 6, 'Gross Total', 1, 0, 'L');
    $pdf->Cell(80, 6, number_format($gross, 2), 1, 1, 'R');

    $pdf->Ln(5);

    // Deduction rows
    $pdf->SetFont('helvetica', 'B', 10);
    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
        $pdf->AddPage();
    }
    $pdf->Cell(180, 6, 'Deductions', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    if ($deductionSummarys) {
        foreach ($deductionSummarys as $index => $deduction_item) {
            if (!isset($deduction_item['value']) || is_array($deduction_item['value']) || !is_numeric($deduction_item['value'])) {
                error_log('Invalid deduction value for ' . ($deduction_item['edDesc'] ?? 'unknown') . ': ' . json_encode($deduction_item['value'] ?? 'unset'));
                continue;
            }
            $value = floatval($deduction_item['value']);
            if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
                $pdf->AddPage();
                $pdf->SetFillColor(200, 200, 200);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(100, 8, 'Description', 1, 0, 'L', 1);
                $pdf->Cell(80, 8, 'Amount', 1, 1, 'R', 1);
                $pdf->SetFont('helvetica', '', 10);
            }
            $descLines = $pdf->getNumLines($deduction_item['edDesc'] ?? '', 100);
            $rowHeight = max(6, 6 * $descLines);
            $pdf->MultiCell(100, $rowHeight, $deduction_item['edDesc'] ?? '', 1, 'L', false, 0);
            $pdf->Cell(80, $rowHeight, number_format($value, 2), 1, 1, 'R');
            $deduction += $value;
        }
    } else {
        $pdf->Cell(180, 6, 'No deductions for this period.', 1, 1, 'L');
    }

    // Deduction total
    $pdf->SetFont('helvetica', 'B', 10);
    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
        $pdf->AddPage();
    }
    $pdf->Cell(100, 6, 'Deduction Total', 1, 0, 'L');
    $pdf->Cell(80, 6, number_format($deduction, 2), 1, 1, 'R');

    // Net total
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    if ($pdf->GetY() + 8 > $pdf->getPageHeight() - 15) {
        $pdf->AddPage();
    }
    $pdf->Cell(100, 8, 'Net Total', 1, 0, 'L');
    $pdf->Cell(80, 8, number_format($gross - $deduction, 2), 1, 1, 'R');

    // Generate PDF file
    $filename = 'Payroll_Summary_' . str_replace(' ', '_', $periodDesc) . '.pdf';
    $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
    $pdf->Output($filePath, 'F'); // Save to temp file

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
        $pdf->Output($filename, 'D');
        exit();
    }
} else {
    echo 'Invalid request. Please provide a valid period.';
}
?>