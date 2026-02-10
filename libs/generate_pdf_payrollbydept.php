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
    $businessNameRaw = $businessInfo['business_name'] ?? '';
    $businessName = str_replace(',', ",\n", (string)$businessNameRaw);

    // Get logged-in user details
    $userDetails = $App->getUsersDetails($_SESSION['SESS_MEMBER_ID']);
    if (!$userDetails) {
        error_log('Failed to retrieve user details for user ID: ' . $_SESSION['SESS_MEMBER_ID']);
        die('Error: Unable to retrieve user details.');
    }
    $printedBy = $userDetails['NAME'] ?? '';

    // Get department payroll data
    $payrollData = $App->getBankSummaryGroupBy($period, 'master_staff.DEPTCD', $deptcd);
    if (!$payrollData) {
        error_log('No payroll data for period: ' . $period . ', deptcd: ' . ($deptcd ?? 'null'));
        die('Error: No payroll data available for the selected period and department.');
    }
    error_log('Payroll Data: ' . json_encode($payrollData));

    // Initialize TCPDF
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($printedBy);
    $pdf->SetTitle('Department Payroll Report - ' . $periodDesc);
    $pdf->SetSubject('Department Payroll Report');

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
    $pdf->Cell(0, 5, 'DEPARTMENT PAYROLL REPORT', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Period: ' . $periodDesc, 0, 1, 'C');
    if ($deptcd) {
        $deptDetails = $App->getDeptDetails($deptcd);
        $deptName = $deptDetails['dept'] ?? 'Unknown';
        $pdf->Cell(0, 5, 'Department: ' . $deptName, 0, 1, 'C');
    }
    $pdf->Ln(5);

    // Table header
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(10, 8, 'S/N', 1, 0, 'L', 1);
    $pdf->Cell(40, 8, $deptcd ? 'Name' : 'Department Name', 1, 0, 'L', 1);
    if ($deptcd) {
        $pdf->Cell(20, 8, 'Salary Structure', 1, 0, 'L', 1);
        $pdf->Cell(10, 8, 'Grade', 1, 0, 'C', 1);
        $pdf->Cell(10, 8, 'Step', 1, 0, 'C', 1);
    }
    $pdf->Cell(15, 8, 'No of Staff', 1, 0, 'R', 1);
    $pdf->Cell(25, 8, 'Total Allowance', 1, 0, 'R', 1);
    $pdf->Cell(25, 8, 'Total Deduction', 1, 0, 'R', 1);
    $pdf->Cell(25, 8, 'Net', 1, 1, 'R', 1);

    // Table data
    $pdf->SetFont('helvetica', '', 8);
    $totalAllow = $totalDeduc = $totalNet = $totalStaff = 0.0;
    $sn = 1;
    foreach ($payrollData as $row) {
        $allow = is_numeric($row['allow']) ? floatval($row['allow']) : 0.0;
        $deduc = is_numeric($row['deduc']) ? floatval($row['deduc']) : 0.0;
        $net = is_numeric($row['net']) ? floatval($row['net']) : 0.0;
        $staff_count = is_numeric($row['staff_count']) ? intval($row['staff_count']) : 0;

        // Calculate row height based on content
        $rowHeight = 6;
        $nameText = $deptcd ? ($row['NAME'] ?? '') : ($row['dept'] ?? '');
        $nameLines = $pdf->getNumLines($nameText, 40);
        $salaryTypeLines = $deptcd ? $pdf->getNumLines($row['SalaryType'] ?? '', 20) : 1;
        $rowHeight = max($rowHeight, 6 * max($nameLines, $salaryTypeLines));

        // Check for page break
        if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - 15) {
            $pdf->AddPage();
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(10, 8, 'S/N', 1, 0, 'L', 1);
            $pdf->Cell(40, 8, $deptcd ? 'Name' : 'Department Name', 1, 0, 'L', 1);
            if ($deptcd) {
                $pdf->Cell(20, 8, 'Salary Structure', 1, 0, 'L', 1);
                $pdf->Cell(10, 8, 'Grade', 1, 0, 'C', 1);
                $pdf->Cell(10, 8, 'Step', 1, 0, 'C', 1);
            }
            $pdf->Cell(15, 8, 'No of Staff', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, 'Total Allowance', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, 'Total Deduction', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, 'Net', 1, 1, 'R', 1);
            $pdf->SetFont('helvetica', '', 8);
        }

        $pdf->Cell(10, $rowHeight, $sn, 1, 0, 'L');
        $pdf->MultiCell(40, $rowHeight, $nameText, 1, 'L', false, 0);
        if ($deptcd) {
            $pdf->MultiCell(20, $rowHeight, $row['SalaryType'] ?? '', 1, 'L', false, 0);
            $pdf->Cell(10, $rowHeight, intval($row['grade'] ?? 0), 1, 0, 'C');
            $pdf->Cell(10, $rowHeight, intval($row['step'] ?? 0), 1, 0, 'C');
        }
        $pdf->Cell(15, $rowHeight, $staff_count, 1, 0, 'R');
        $pdf->Cell(25, $rowHeight, number_format($allow, 2), 1, 0, 'R');
        $pdf->Cell(25, $rowHeight, number_format($deduc, 2), 1, 0, 'R');
        $pdf->Cell(25, $rowHeight, number_format($net, 2), 1, 1, 'R');

        $totalAllow += $allow;
        $totalDeduc += $deduc;
        $totalNet += $net;
        $totalStaff += $staff_count;
        $sn++;
    }

    // Totals
    $pdf->SetFont('helvetica', 'B', 8);
    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
        $pdf->AddPage();
    }
    $colSpanWidth = $deptcd ? 90 : 50;
    $pdf->Cell($colSpanWidth, 6, 'Total', 1, 0, 'R');
    $pdf->Cell(15, 6, $totalStaff, 1, 0, 'R');
    $pdf->Cell(25, 6, number_format($totalAllow, 2), 1, 0, 'R');
    $pdf->Cell(25, 6, number_format($totalDeduc, 2), 1, 0, 'R');
    $pdf->Cell(25, 6, number_format($totalNet, 2), 1, 1, 'R');

    // Generate PDF file
    $filename = 'Dept_Payroll_Report_' . str_replace(' ', '_', $periodDesc) . '.pdf';
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
        $pdf->Output($filename, 'D');
        exit();
    }
} else {
    echo 'Invalid request. Please provide a valid period and department.';
}
?>