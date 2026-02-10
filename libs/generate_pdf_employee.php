<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
require_once '../vendor/tecnickcom/tcpdf/tcpdf.php'; // Ensure TCPDF is installed via Composer

$employees = $App->getEmployeeDetails(); // Fetch employee data

// Create new PDF document
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // Landscape orientation

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('TASCE Payroll');
$pdf->SetTitle('Employee List');
$pdf->SetSubject('Employee List Report');
$pdf->SetKeywords('Employee, Report, TASCE');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Business name and report name
$businessName = "Sikiru Adetona College of Education, Science and Technology, Omu-Ajose";
$reportName = "Employee List";

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, $businessName, 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, $reportName, 0, 1, 'C');
$pdf->Ln(5);

// Table headers
$headers = ['Staff ID', 'Name', 'TIN', 'Employment Type', 'Gender', 'Email', 'Date of Birth', 'Department', 'PFA', 'PFA PIN.', 'Bank', 'Account No.', 'SALARY TYPE', 'Grade', 'Step', 'Status'];
// Adjusted widths to better accommodate wrapping
$colWidths = [15, 30, 20, 20, 12, 30, 15, 20, 20, 15, 20, 20, 15, 10, 10, 15]; // Total width ~277mm (A4 landscape minus margins)

// Header styling
$pdf->SetFillColor(200, 200, 200);
$pdf->SetFont('helvetica', 'B', 8);
for ($i = 0; $i < count($headers); $i++) {
    $pdf->MultiCell($colWidths[$i], 7, $headers[$i], 1, 'C', 1, 0);
}
$pdf->Ln();

// Data rows
$pdf->SetFont('helvetica', '', 8);
foreach ($employees as $employee) {
    // Prepare the data for this row
    $rowData = [
        $employee['OGNO'],
        $employee['NAME'],
        $employee['TIN'],
        $employee['employment_type'],
        $employee['GENDER'],
        $employee['EMAIL'],
        $employee['DOB'],
        $employee['dept'],
        $employee['PFANAME'],
        $employee['PFAACCTNO'],
        $employee['BNAME'],
        $employee['ACCTNO'],
        $employee['SalaryType'],
        $employee['GRADE'],
        $employee['STEP'],
        $employee['STATUS']
    ];

    // Calculate the maximum height for this row
    $maxHeight = 0;
    for ($i = 0; $i < count($rowData); $i++) {
        $numLines = $pdf->getNumLines($rowData[$i], $colWidths[$i]);
        $cellHeight = $numLines * 4; // Approximate height per line (adjust based on font size)
        $maxHeight = max($maxHeight, $cellHeight);
    }

    // Store the current Y position to reset after each row
    $yStart = $pdf->GetY();

    // Draw each cell in the row with MultiCell
    for ($i = 0; $i < count($rowData); $i++) {
        $x = $pdf->GetX();
        $pdf->MultiCell($colWidths[$i], $maxHeight, $rowData[$i], 1, 'L', 0, 0);
        $pdf->SetXY($x + $colWidths[$i], $yStart); // Move to the next column, same row
    }

    // Move to the next row
    $pdf->Ln($maxHeight);

    // Check if we need a new page
    if ($pdf->GetY() > $pdf->getPageHeight() - 10) {
        $pdf->AddPage();
        // Redraw headers on the new page
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('helvetica', 'B', 8);
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->MultiCell($colWidths[$i], 7, $headers[$i], 1, 'C', 1, 0);
        }
        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 8);
    }
}

// Output the PDF
$filename = 'employees.pdf';
$pdf->Output($filename, 'D'); // 'D' forces download
exit();
?>