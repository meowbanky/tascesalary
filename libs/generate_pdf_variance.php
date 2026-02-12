<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

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

if (isset($_GET['month1']) && isset($_GET['month2'])) {
    $month1 = $_GET['month1'];
    $month2 = $_GET['month2'];

    // Validate inputs
    if (empty($month1) || empty($month2)) {
        error_log('Invalid inputs: month1=' . $month1 . ', month2=' . $month2);
        die('Error: Both months are required.');
    }
    if ($month1 === $month2) {
        error_log('Same months selected: month1=' . $month1 . ', month2=' . $month2);
        die('Error: Please select two different months.');
    }

    // Get period descriptions
    $month1Descs = $App->getPeriodDescription($month1);
    $month2Descs = $App->getPeriodDescription($month2);
    if (!$month1Descs || !$month2Descs) {
        error_log('Invalid period description: month1=' . $month1 . ', month2=' . $month2);
        die('Error: Invalid or missing period description.');
    }
    $month1Desc = $month1Descs['period'];
    $month2Desc = $month2Descs['period'];

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

    // Fetch variance data (replicating get_variance.php logic)
    try {
        $stmt = $App->link->prepare("SELECT
            COALESCE(t1.staff_id, t2.staff_id) AS staff_id,
            COALESCE(t1.OGNO, t2.OGNO) AS OGNO,
            COALESCE(t1.name, t2.name) AS name,
            COALESCE(t1.gross_salary, 0) AS gross_salary_month1,
            COALESCE(t2.gross_salary, 0) AS gross_salary_month2,
            COALESCE(t2.gross_salary, 0) - COALESCE(t1.gross_salary, 0) AS salary_difference,
            vr.remark
        FROM
            (SELECT
                sum(tbl_master.allow) AS gross_salary, 
                tbl_master.staff_id,
                employee.OGNO,
                employee.`NAME` AS name
            FROM
                tbl_master
            INNER JOIN
                employee ON tbl_master.staff_id = employee.staff_id
            WHERE
                period = :month1
            GROUP BY
                tbl_master.staff_id) t1
        LEFT JOIN
            (SELECT
                sum(tbl_master.allow) AS gross_salary, 
                tbl_master.staff_id,
                employee.OGNO,
                employee.`NAME` AS name
            FROM
                tbl_master
            INNER JOIN
                employee ON tbl_master.staff_id = employee.staff_id
            WHERE
                period = :month2
            GROUP BY
                tbl_master.staff_id) t2
        ON
            t1.staff_id = t2.staff_id
        LEFT JOIN
            variance_remarks vr ON t1.staff_id = vr.staff_id 
            AND vr.month1_period_id = :month1 
            AND vr.month2_period_id = :month2
        UNION
        SELECT
            COALESCE(t1.staff_id, t2.staff_id) AS staff_id,
            COALESCE(t1.OGNO, t2.OGNO) AS OGNO,
            COALESCE(t1.name, t2.name) AS name,
            COALESCE(t1.gross_salary, 0) AS gross_salary_month1,
            COALESCE(t2.gross_salary, 0) AS gross_salary_month2,
            COALESCE(t2.gross_salary, 0) - COALESCE(t1.gross_salary, 0) AS salary_difference,
            vr.remark
        FROM
            (SELECT
                sum(tbl_master.allow) AS gross_salary, 
                tbl_master.staff_id,
                employee.OGNO,
                employee.`NAME` AS name
            FROM
                tbl_master
            INNER JOIN
                employee ON tbl_master.staff_id = employee.staff_id
            WHERE
                period = :month1
            GROUP BY
                tbl_master.staff_id) t1
        RIGHT JOIN
            (SELECT
                sum(tbl_master.allow) AS gross_salary, 
                tbl_master.staff_id,
                employee.OGNO,
                employee.`NAME` AS name
            FROM
                tbl_master
            INNER JOIN
                employee ON tbl_master.staff_id = employee.staff_id
            WHERE
                period = :month2
            GROUP BY
                tbl_master.staff_id) t2
        ON
            t1.staff_id = t2.staff_id
        LEFT JOIN
            variance_remarks vr ON t2.staff_id = vr.staff_id 
            AND vr.month1_period_id = :month1 
            AND vr.month2_period_id = :month2");
        $stmt->execute([':month1' => $month1, ':month2' => $month2]);
        $salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare data
        $data = [];
        $totalDifference = 0.0;
        foreach ($salaries as $salary) {
            $data[] = [
                'staff_id' => $salary['OGNO'],
                'name' => $salary['name'],
                'month1_gross' => $salary['gross_salary_month1'],
                'month2_gross' => $salary['gross_salary_month2'],
                'difference' => $salary['salary_difference'],
                'remark' => $salary['remark'] ?? '',
            ];
            $totalDifference += floatval($salary['salary_difference']);
        }

        if (empty($data)) {
            error_log('No data for month1=' . $month1 . ', month2=' . $month2);
            die('Error: No data available for the selected months.');
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        die('Error: Database error occurred.');
    }

    // Initialize TCPDF
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($printedBy);
    $pdf->SetTitle('Variance Report - ' . $month1Desc . ' vs ' . $month2Desc);
    $pdf->SetSubject('Variance Report');

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
    $pdf->Cell(0, 5, 'VARIANCE REPORT', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Period: ' . $month1Desc . ' vs ' . $month2Desc, 0, 1, 'C');
    $pdf->Ln(5);

    // Table header
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->Cell(15, 8, 'Staff ID', 1, 0, 'L', 1);
    $pdf->Cell(35, 8, 'Name', 1, 0, 'L', 1);
    $pdf->Cell(25, 8, $month1Desc, 1, 0, 'R', 1);
    $pdf->Cell(25, 8, $month2Desc, 1, 0, 'R', 1);
    $pdf->Cell(25, 8, 'Difference', 1, 0, 'R', 1);
    $pdf->Cell(50, 8, 'Remark', 1, 1, 'L', 1);

    // Table data
    $pdf->SetFont('helvetica', '', 7);
    foreach ($data as $row) {
        // Check for page break
        if ($pdf->GetY() + 10 > $pdf->getPageHeight() - 15) {
            $pdf->AddPage();
            // Redraw table header on new page
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell(15, 8, 'Staff ID', 1, 0, 'L', 1);
            $pdf->Cell(35, 8, 'Name', 1, 0, 'L', 1);
            $pdf->Cell(25, 8, $month1Desc, 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $month2Desc, 1, 0, 'R', 1);
            $pdf->Cell(25, 8, 'Difference', 1, 0, 'R', 1);
            $pdf->Cell(50, 8, 'Remark', 1, 1, 'L', 1);
            $pdf->SetFont('helvetica', '', 7);
        }

        // Calculate row height
        $nameLines = $pdf->getNumLines($row['name'] ?? '', 35);
        $remarkLines = $pdf->getNumLines($row['remark'] ?? '', 50);
        $rowHeight = max(6, 6 * $nameLines, 6 * $remarkLines);

        $pdf->Cell(15, $rowHeight, $row['staff_id'] ?? '', 1, 0, 'L');
        $pdf->MultiCell(35, $rowHeight, $row['name'] ?? '', 1, 'L', false, 0);
        $pdf->Cell(25, $rowHeight, number_format($row['month1_gross'], 2), 1, 0, 'R');
        $pdf->Cell(25, $rowHeight, number_format($row['month2_gross'], 2), 1, 0, 'R');
        $pdf->Cell(25, $rowHeight, number_format($row['difference'], 2), 1, 0, 'R');
        $pdf->MultiCell(50, $rowHeight, $row['remark'] ?? '', 1, 'L', false, 1);
    }

    // Totals
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetX(15);
    $pdf->Cell(15, 6, '', 1, 0, 'R');
    $pdf->Cell(35, 6, 'Difference Total', 1, 0, 'R');
    $pdf->Cell(25, 6, '', 1, 0, 'R');
    $pdf->Cell(25, 6, '', 1, 0, 'R');
    $pdf->Cell(25, 6, number_format($totalDifference, 2), 1, 0, 'R');
    $pdf->Cell(50, 6, '', 1, 1, 'R');

    // Output the PDF
    $filename = 'Variance_Report_' . str_replace(' ', '_', $month1Desc) . '_vs_' . str_replace(' ', '_', $month2Desc) . '.pdf';
    $pdf->Output($filename, 'D');
    exit();
} else {
    echo 'Invalid request. Please provide valid months.';
}
?>