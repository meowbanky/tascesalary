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
    $pfaName = 'All PFAs';
    if ($pfa == -1) {
        $pfaName = 'PFA Analysis';
    } elseif ($pfa != -2) {
        $pfaName = $App->selectDrop("SELECT PFANAME FROM tbl_pfa WHERE PFACODE = :pfa", ['pfa' => $pfa])[0]['PFANAME'] ?? 'Unknown PFA';
    }
    if ($pfa != -1 && $pfa != -2 && $pfaName == 'Unknown PFA') {
        error_log('Invalid PFA code: ' . $pfa);
        die('Error: Invalid PFA selection.');
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

    // Get pension data
    $getPensions = $App->getPfa($period, $pfa);
    if (!$getPensions || !is_array($getPensions)) {
        error_log('No data or invalid data for period: ' . $period . ', pfa: ' . $pfa);
        die('Error: No data available for the selected period and PFA.');
    }

    // Initialize TCPDF
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($printedBy);
    $pdf->SetTitle('Pension Report - ' . $periodDesc);
    $pdf->SetSubject('Pension Report');

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
    $pdf->Cell(0, 5, 'PENSION REPORT', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Period: ' . $periodDesc, 0, 1, 'C');
    $pdf->Cell(0, 5, 'PFA: ' . $pfaName, 0, 1, 'C');
    $pdf->Ln(5);

    // Table header
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->Cell(15, 8, 'S/N', 1, 0, 'L', 1);
    if ($pfa != -1) {
        $pdf->Cell(20, 8, 'Staff No', 1, 0, 'L', 1);
        $pdf->Cell(45, 8, 'Name', 1, 0, 'L', 1);
        $pdf->Cell(30, 8, 'PFA PIN', 1, 0, 'L', 1);
    }
    $pdf->Cell($pfa == -1 ? 130 : 35, 8, 'PFA', 1, 0, 'L', 1);
    $pdf->Cell(30, 8, 'AMOUNT', 1, 1, 'R', 1);

    // Table data
    $pdf->SetFont('helvetica', '', 7);
    $grossPension = 0.0;
    $sn = 1;
    
    if ($pfa == -1) {
        // Group PFAs into categories
        $regularPFAs = [];
        $suspendedPFAs = [];
        $othersPFAs = [];
        
        foreach ($getPensions as $getPension) {
            $pfaCode = $getPension['PFACODE'] ?? null;
            if ($pfaCode == 26) {
                $suspendedPFAs[] = $getPension;
            } elseif ($pfaCode == 21) {
                $othersPFAs[] = $getPension;
            } else {
                $regularPFAs[] = $getPension;
            }
        }
        
        // Display Regular PFAs group
        if (!empty($regularPFAs)) {
            $pdf->SetFillColor(168, 213, 255);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(175, 8, 'REGULAR PFAs', 1, 1, 'L', 1);
            $pdf->SetFont('helvetica', '', 7);
            
            $regularTotal = 0;
            foreach ($regularPFAs as $getPension) {
                $pfaWidth = 130;
                $pfaLines = $pdf->getNumLines($getPension['PFANAME'] ?? '', $pfaWidth);
                $rowHeight = max(6, 6 * $pfaLines);
                
                if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - 15) {
                    $pdf->AddPage();
                }
                
                $pdf->Cell(15, $rowHeight, $sn, 1, 0, 'L');
                $pdf->MultiCell($pfaWidth, $rowHeight, $getPension['PFANAME'] ?? '', 1, 'L', false, 0);
                $pdf->Cell(30, $rowHeight, number_format($getPension['deduc'], 2), 1, 1, 'R');
                
                $regularTotal += floatval($getPension['deduc']);
                $grossPension += floatval($getPension['deduc']);
                $sn++;
            }
            
            // Subtotal row
            $pdf->SetFillColor(232, 232, 232);
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell(145, 6, 'Subtotal', 1, 0, 'R', 1);
            $pdf->Cell(30, 6, number_format($regularTotal, 2), 1, 1, 'R', 1);
            $pdf->SetFont('helvetica', '', 7);
        }
        
        // Display Suspended group
        if (!empty($suspendedPFAs)) {
            $pdf->SetFillColor(255, 235, 156);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(175, 8, 'SUSPENDED', 1, 1, 'L', 1);
            $pdf->SetFont('helvetica', '', 7);
            
            $suspendedTotal = 0;
            foreach ($suspendedPFAs as $getPension) {
                $pfaWidth = 130;
                $pfaLines = $pdf->getNumLines($getPension['PFANAME'] ?? '', $pfaWidth);
                $rowHeight = max(6, 6 * $pfaLines);
                
                if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - 15) {
                    $pdf->AddPage();
                }
                
                $pdf->Cell(15, $rowHeight, $sn, 1, 0, 'L');
                $pdf->MultiCell($pfaWidth, $rowHeight, $getPension['PFANAME'] ?? '', 1, 'L', false, 0);
                $pdf->Cell(30, $rowHeight, number_format($getPension['deduc'], 2), 1, 1, 'R');
                
                $suspendedTotal += floatval($getPension['deduc']);
                $grossPension += floatval($getPension['deduc']);
                $sn++;
            }
            
            // Subtotal row
            $pdf->SetFillColor(232, 232, 232);
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell(145, 6, 'Subtotal', 1, 0, 'R', 1);
            $pdf->Cell(30, 6, number_format($suspendedTotal, 2), 1, 1, 'R', 1);
            $pdf->SetFont('helvetica', '', 7);
        }
        
        // Display Others group
        if (!empty($othersPFAs)) {
            $pdf->SetFillColor(255, 199, 206);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(175, 8, 'OTHERS', 1, 1, 'L', 1);
            $pdf->SetFont('helvetica', '', 7);
            
            $othersTotal = 0;
            foreach ($othersPFAs as $getPension) {
                $pfaWidth = 130;
                $pfaLines = $pdf->getNumLines($getPension['PFANAME'] ?? '', $pfaWidth);
                $rowHeight = max(6, 6 * $pfaLines);
                
                if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - 15) {
                    $pdf->AddPage();
                }
                
                $pdf->Cell(15, $rowHeight, $sn, 1, 0, 'L');
                $pdf->MultiCell($pfaWidth, $rowHeight, $getPension['PFANAME'] ?? '', 1, 'L', false, 0);
                $pdf->Cell(30, $rowHeight, number_format($getPension['deduc'], 2), 1, 1, 'R');
                
                $othersTotal += floatval($getPension['deduc']);
                $grossPension += floatval($getPension['deduc']);
                $sn++;
            }
            
            // Subtotal row
            $pdf->SetFillColor(232, 232, 232);
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell(145, 6, 'Subtotal', 1, 0, 'R', 1);
            $pdf->Cell(30, 6, number_format($othersTotal, 2), 1, 1, 'R', 1);
            $pdf->SetFont('helvetica', '', 7);
        }
    } else {
        // Single PFA - display individual records
        foreach ($getPensions as $getPension) {
            // Calculate row height
            $pfaWidth = 35;
            $nameLines = $pdf->getNumLines($getPension['NAME'] ?? '', 45);
            $pfaLines = $pdf->getNumLines($getPension['PFANAME'] ?? '', $pfaWidth);
            $rowHeight = max(6, 6 * max($nameLines, $pfaLines));

            // Check for page break
            if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - 15) {
                $pdf->AddPage();
                $pdf->SetFillColor(200, 200, 200);
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell(15, 8, 'S/N', 1, 0, 'L', 1);
                $pdf->Cell(20, 8, 'Staff No', 1, 0, 'L', 1);
                $pdf->Cell(45, 8, 'Name', 1, 0, 'L', 1);
                $pdf->Cell(30, 8, 'PFA PIN', 1, 0, 'L', 1);
                $pdf->Cell($pfaWidth, 8, 'PFA', 1, 0, 'L', 1);
                $pdf->Cell(30, 8, 'AMOUNT', 1, 1, 'R', 1);
                $pdf->SetFont('helvetica', '', 7);
            }

            $pdf->Cell(15, $rowHeight, $sn, 1, 0, 'L');
            $pdf->Cell(20, $rowHeight, $getPension['OGNO'] ?? '', 1, 0, 'L');
            $pdf->MultiCell(45, $rowHeight, $getPension['NAME'] ?? '', 1, 'L', false, 0);
            $pdf->Cell(30, $rowHeight, $getPension['PFAACCTNO'] ?? '', 1, 0, 'L');
            $pdf->MultiCell($pfaWidth, $rowHeight, $getPension['PFANAME'] ?? '', 1, 'L', false, 0);
            $pdf->Cell(30, $rowHeight, number_format($getPension['deduc'], 2), 1, 1, 'R');

            $grossPension += floatval($getPension['deduc']);
            $sn++;
        }
    }

    // Totals
    $pdf->SetFont('helvetica', 'B', 7);
    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 15) {
        $pdf->AddPage();
    }
    $totalWidth = 145;
    $pdf->Cell($totalWidth, 6, 'Total', 1, 0, 'R');
    $pdf->Cell(30, 6, number_format($grossPension, 2), 1, 1, 'R');

    // Generate PDF file
    $filename = 'Pension_Report_' . str_replace(' ', '_', $periodDesc) . '.pdf';
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
        $pdf->Output($filename, 'D');
        exit();
    }
} else {
    echo 'Invalid request. Please provide a valid period and PFA.';
}
?>