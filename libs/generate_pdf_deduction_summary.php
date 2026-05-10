<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once 'App.php';

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

if (isset($_GET['payperiod'])) {
    $period = $_GET['payperiod'];
    $summary = $App->getDeductionSummaryWithPayees($period);
    
    $periodDesc = $App->getPeriodDescription($period);
    $periodText = $periodDesc['period'] ?? '';
    
    // Get business name and current user
    $businessName = $_SESSION['businessname'] ?? 'TASCE';
    $userDetails = $App->getUsersDetails($_SESSION['SESS_MEMBER_ID']);
    $printedBy = $userDetails['NAME'] ?? '';
    $currentDate = date('Y-m-d H:i:s');

    // Initialize TCPDF
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($printedBy);
    $pdf->SetTitle('Deduction Summary Report - ' . $periodText);
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->setCustomFooterData($printedBy, $currentDate);
    
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();
    
    // Logos
    $logoLeft = '../assets/images/ogun_logo.png';
    $logoRight = '../assets/images/tasce_r_logo.png';
    if (file_exists($logoLeft) && file_exists($logoRight)) {
        $pdf->Image($logoLeft, 15, 10, 20, 20, 'PNG', '', 'T', false, 300, '', false, false, 0);
        $pdf->Image($logoRight, 175, 10, 20, 20, 'PNG', '', 'T', false, 300, '', false, false, 0);
    }

    // Header Content
    $pdf->SetY(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 7, 'SIKIRU ADETONA COLLEGE OF EDUCATION SCIENCE AND TECHNOLOGY, OMU-AJOSE', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 10);
    $formattedPeriod = str_replace('-', '. ', strtoupper($periodText));
    $pdf->Cell(0, 7, 'DEDUCTIONS FOR THE MONTH OF ' . $formattedPeriod, 0, 1, 'C');
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(10, 8, 'S/N', 1, 0, 'C', 1);
    $pdf->Cell(65, 8, 'PAYEE', 1, 0, 'L', 1);
    $pdf->Cell(45, 8, 'BANK NAME', 1, 0, 'L', 1);
    $pdf->Cell(35, 8, 'ACCOUNT NO', 1, 0, 'L', 1);
    $pdf->Cell(25, 8, 'AMOUNT', 1, 1, 'R', 1);

    // Table Data
    $pdf->SetFont('helvetica', '', 9);
    $sn = 1;
    $total = 0;
    if ($summary) {
        foreach ($summary as $item) {
            // Only include entries with bank details as requested
            if (empty($item['bank_name']) || empty($item['account_no'])) {
                continue;
            }
            
            $total += $item['amount'];
            
            // Check for page break
            if ($pdf->GetY() + 8 > $pdf->getPageHeight() - 20) {
                $pdf->AddPage();
                // Repeat header on new page
                $pdf->SetFillColor(230, 230, 230);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(10, 8, 'S/N', 1, 0, 'C', 1);
                $pdf->Cell(65, 8, 'PAYEE', 1, 0, 'L', 1);
                $pdf->Cell(45, 8, 'BANK NAME', 1, 0, 'L', 1);
                $pdf->Cell(35, 8, 'ACCOUNT NO', 1, 0, 'L', 1);
                $pdf->Cell(25, 8, 'AMOUNT', 1, 1, 'R', 1);
                $pdf->SetFont('helvetica', '', 9);
            }

            $pdf->Cell(10, 8, $sn++, 1, 0, 'C');
            $pdf->Cell(65, 8, $item['payee_name'], 1, 0, 'L');
            $pdf->Cell(45, 8, $item['bank_name'], 1, 0, 'L');
            $pdf->Cell(35, 8, $item['account_no'], 1, 0, 'L');
            $pdf->Cell(25, 8, number_format($item['amount'], 2), 1, 1, 'R');
        }
    }

    // Total Row
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(155, 8, 'TOTAL', 1, 0, 'R', 1);
    $pdf->Cell(25, 8, number_format($total, 2), 1, 1, 'R', 1);

    // Output PDF
    $pdf->Output('Deduction_Summary_' . str_replace(' ', '_', $periodText) . '.pdf', 'D');
    exit();
}
?>
