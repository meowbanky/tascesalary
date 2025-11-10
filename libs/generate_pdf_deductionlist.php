<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Custom TCPDF class for footer
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
    $businessNameRaw = $businessInfo['business_name'] ?? '';
    $businessName = str_replace(',', ",\n", (string)$businessNameRaw);

    // Get logged-in user details
    $userDetails = $App->getUsersDetails($_SESSION['SESS_MEMBER_ID']);
    if (!$userDetails) {
        error_log('Failed to retrieve user details for user ID: ' . $_SESSION['SESS_MEMBER_ID']);
        die('Error: Unable to retrieve user details.');
    }
    $printedBy = $userDetails['NAME'] ?? '';

    // Get deduction/allowance data
    $deductions = $App->getReportDeductionList($period, $type, $allow_id);
    if (!$deductions) {
        error_log('No data for period: ' . $period . ', allow_id: ' . $allow_id . ', type: ' . $type);
        die('Error: No data available for the selected period and allowance/deduction.');
    }

    // Initialize TCPDF
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($printedBy);
    $pdf->SetTitle($allow_deduc . ' Report - ' . $periodDesc);
    $pdf->SetSubject($allow_deduc . ' Report');

    // Enable header and footer
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
    $pdf->SetFont('helvetica', '', 8);

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
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 5, strtoupper($allow_deduc . ' REPORT'), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Period: ' . $periodDesc, 0, 1, 'C');
    $pdf->Cell(0, 5, $allow_deduc . ': ' . $allowDescription, 0, 1, 'C');
    $pdf->Ln(5);

    // Table header
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(10, 8, 'S/N', 1, 0, 'L', 1);
    $pdf->Cell(30, 8, 'Staff No', 1, 0, 'L', 1);
    $pdf->Cell(80, 8, 'Name', 1, 0, 'L', 1);
    $pdf->Cell(40, 8, 'Amount', 1, 1, 'R', 1);

    // Table data
    $pdf->SetFont('helvetica', '', 8);
    $gross = 0.0;
    $sn = 1;
    foreach ($deductions as $deduction) {
        $value = is_numeric($deduction['value']) ? floatval($deduction['value']) : 0.0;
        $nameLines = $pdf->getNumLines($deduction['NAME'] ?? '', 80);
        $rowHeight = max(6, 6 * $nameLines);

        // Check for page break
        if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - 15) {
            $pdf->AddPage();
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(10, 8, 'S/N', 1, 0, 'L', 1);
            $pdf->Cell(30, 8, 'Staff No', 1, 0, 'L', 1);
            $pdf->Cell(80, 8, 'Name', 1, 0, 'L', 1);
            $pdf->Cell(40, 8, 'Amount', 1, 1, 'R', 1);
            $pdf->SetFont('helvetica', '', 8);
        }

        $pdf->Cell(10, $rowHeight, $sn, 1, 0, 'L');
        $pdf->Cell(30, $rowHeight, $deduction['OGNO'] ?? '', 1, 0, 'L');
        $pdf->MultiCell(80, $rowHeight, $deduction['NAME'] ?? '', 1, 'L', false, 0);
        $pdf->Cell(40, $rowHeight, number_format($value, 2), 1, 1, 'R');

        $gross += $value;
        $sn++;
    }

    // Totals
    $pdf->SetFont('helvetica', 'B', 8);
    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
        $pdf->AddPage();
    }
    $pdf->Cell(120, 6, 'Total', 1, 0, 'R');
    $pdf->Cell(40, 6, number_format($gross, 2), 1, 1, 'R');

    // Generate PDF file
    $filename = $allow_deduc . '_Report_' . str_replace(' ', '_', $periodDesc) . '.pdf';
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
        $pdf->Output($filename, 'D');
        exit();
    }
} else {
    echo 'Invalid request. Please provide a valid period, allowance/deduction, and type.';
}
?>