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

    // Get bank name
    $bankName = ($bank == -1) ? 'All Banks' : $App->getBankName($bank)['BNAME'] ?? 'Unknown Bank';
    if ($bank != -1 && $bankName == 'Unknown Bank') {
        error_log('Invalid bank ID: ' . $bank);
        die('Error: Invalid bank selection.');
    }

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

    // Get net to bank data
    $grossPays = $App->getBankSummary($period, $bank);
    if (!$grossPays || !is_array($grossPays)) {
        error_log('No data or invalid data for period: ' . $period . ', bank: ' . $bank);
        die('Error: No data available for the selected period and bank.');
    }
    error_log('GrossPays count: ' . count($grossPays));

    // Initialize TCPDF
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($printedBy);
    $pdf->SetTitle('Net to Bank Report - ' . $periodDesc);
    $pdf->SetSubject('Net to Bank Report');

    // Enable header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // Set custom footer data
    $currentDate = date('Y-m-d H:i:s');
    $pdf->setCustomFooterData($printedBy, $currentDate);

    // Set margins
    $pdf->SetMargins(15, 20, 20);
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 7);

    // Header with logos and institution name
    $logoLeft = '../assets/images/ogun_logo.png';
    $logoRight = '../assets/images/tasce_r_logo.png';
    if (file_exists($logoLeft) && file_exists($logoRight)) {
        $pdf->Image($logoLeft, 15, 10, 25, 25, 'PNG', '', 'T', false, 300, '', false, false, 0);
        $pdf->Image($logoRight, 165, 10, 25, 25, 'PNG', '', 'T', false, 300, '', false, false, 0);
    } else {
        error_log('Logo files missing: ' . $logoLeft . ' or ' . $logoRight);
    }

    // Institution name
    $pdf->SetY(10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->MultiCell(140, 15, $businessName, 0, 'C', false, 1, 35, null, true, 0, false, true, 15, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(0, 5, 'NET TO BANK REPORT', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Period: ' . $periodDesc, 0, 1, 'C');
    $pdf->Cell(0, 5, 'Bank: ' . $bankName, 0, 1, 'C');
    $pdf->Ln(5);

    // Table header
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->Cell(20, 8, 'S/N', 1, 0, 'L', 1);
    $pdf->Cell(30, 8, 'NAME', 1, 0, 'L', 1);
    $pdf->Cell(15, 8, 'STATUS', 1, 0, 'L', 1);
    $pdf->Cell(15, 8, 'AMOUNT', 1, 0, 'R', 1);
    $pdf->Cell(15, 8, 'PAYMENT DATE', 1, 0, 'L', 1);
    $pdf->Cell(20, 8, 'BENEFICIARY CODE', 1, 0, 'L', 1);
    $pdf->Cell(20, 8, 'ACCOUNT', 1, 0, 'L', 1);
    $pdf->Cell(15, 8, 'BANK CODE', 1, 0, 'L', 1);
    $pdf->Cell(15, 8, 'DEBIT ACCOUNT', 1, 0, 'L', 1);
    $pdf->Cell(20, 8, 'BANK', 1, 1, 'L', 1);

    // Table data
    $pdf->SetFont('helvetica', '', 7);
    $totalAmount = 0.0;
    $sn = 1;
    foreach ($grossPays as $index => $grossPay) {
        // Validate data
        if (!is_array($grossPay) || !isset($grossPay['NAME'])) {
            error_log('Invalid data at index ' . $index . ': ' . json_encode($grossPay));
            continue;
        }

        $net = (is_numeric($grossPay['allow'] ?? 0) && is_numeric($grossPay['deduc'] ?? 0)) ? floatval($grossPay['allow'] - $grossPay['deduc']) : 0.0;
        $padded = str_pad($grossPay['staff_id'] ?? '', 3, "0", STR_PAD_LEFT);
        $sm_padd = str_pad($sn, 3, "0", STR_PAD_LEFT);
        $sn_des = 'TASCE_' . $periodDesc . '_SAL_' . $sm_padd;

        // Calculate row height
        $snLines = $pdf->getNumLines($sn_des, 20);
        $nameLines = $pdf->getNumLines($grossPay['NAME'] ?? '', 30);
        $bankLines = $pdf->getNumLines($grossPay['bankname'] ?? '', 20);
        $rowHeight = max(6, 6 * max($snLines, $nameLines, $bankLines));

        // Check for page break
        if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - 15) {
            $pdf->AddPage();
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell(20, 8, 'S/N', 1, 0, 'L', 1);
            $pdf->Cell(30, 8, 'NAME', 1, 0, 'L', 1);
            $pdf->Cell(15, 8, 'STATUS', 1, 0, 'L', 1);
            $pdf->Cell(15, 8, 'AMOUNT', 1, 0, 'R', 1);
            $pdf->Cell(15, 8, 'PAYMENT DATE', 1, 0, 'L', 1);
            $pdf->Cell(20, 8, 'BENEFICIARY CODE', 1, 0, 'L', 1);
            $pdf->Cell(20, 8, 'ACCOUNT', 1, 0, 'L', 1);
            $pdf->Cell(15, 8, 'BANK CODE', 1, 0, 'L', 1);
            $pdf->Cell(15, 8, 'DEBIT ACCOUNT', 1, 0, 'L', 1);
            $pdf->Cell(20, 8, 'BANK', 1, 1, 'L', 1);
            $pdf->SetFont('helvetica', '', 7);
        }

        try {
            $pdf->MultiCell(20, $rowHeight, $sn_des, 1, 'L', false, 0);
            $pdf->MultiCell(30, $rowHeight, $grossPay['NAME'] ?? '', 1, 'L', false, 0);
            $pdf->Cell(15, $rowHeight, $grossPay['STATUSCD'] ?? '', 1, 0, 'L');
            $pdf->Cell(15, $rowHeight, number_format($net, 2), 1, 0, 'R');
            $pdf->Cell(15, $rowHeight, date('d/m/Y'), 1, 0, 'L');
            $pdf->Cell(20, $rowHeight, 'TASCE ' . $padded, 1, 0, 'L');
            $pdf->Cell(20, $rowHeight, $grossPay['acctno'] ?? '', 1, 0, 'L');
            $pdf->Cell(15, $rowHeight, $grossPay['bankcode'] ?? '', 1, 0, 'L');
            $pdf->Cell(15, $rowHeight, '1229191715', 1, 0, 'L');
            $pdf->MultiCell(20, $rowHeight, $grossPay['bankname'] ?? '', 1, 'L', false, 1);
        } catch (Exception $e) {
            error_log('Error rendering row ' . $sn . ': ' . $e->getMessage());
            continue;
        }

        $totalAmount += $net;
        $sn++;
    }

    // Totals
    $pdf->SetFont('helvetica', 'B', 7);
    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
        $pdf->AddPage();
    }
    $pdf->Cell(65, 6, 'Total', 1, 0, 'R');
    $pdf->Cell(15, 6, number_format($totalAmount, 2), 1, 0, 'R');
    $pdf->Cell(105, 6, '', 1, 1, 'R');

    // Generate PDF file
    $filename = 'Net2Bank_Report_' . str_replace(' ', '_', $periodDesc) . '.pdf';
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
        $pdf->Output($filename, 'D');
        exit();
    }
} else {
    echo 'Invalid request. Please provide a valid period and bank.';
}
?>