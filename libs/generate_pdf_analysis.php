<?php
session_start();
require_once 'App.php';
require_once('../auth_api/lib/tcpdf/tcpdf.php');
$app = new App();
$app->checkAuthentication();

$businessInfo = $app->getBusinessName();
$_SESSION['businessname'] = $businessInfo['business_name'];

if (isset($_GET['period'])) {
    $period = $_GET['period'];
    
    // Get Period Description
    $periodDescSql = "SELECT concat(description,'-',periodYear) as period FROM payperiods WHERE periodId = :period";
    $periodDescStmt = $app->selectOne($periodDescSql, [':period' => $period]);
    $period_description = $periodDescStmt ? $periodDescStmt['period'] : 'Unknown';

    // Build the data similarly to the AJAX endpoint
    try {
        $summarySql = "SELECT 
                        SUM(allow) AS total_gross,
                        (SELECT SUM(deduc) FROM tbl_master WHERE period = :period AND allow_id = 24) AS total_tax
                       FROM tbl_master 
                       WHERE period = :period";
        $summary = $app->selectOne($summarySql, [':period' => $period]);
        
        $gross = (float)($summary['total_gross'] ?? 0);
        $tax = (float)($summary['total_tax'] ?? 0);
        $gross_after_tax = $gross - $tax;

        // Query to calculate gross for suspended staff
        $suspendedSql = "SELECT SUM(m.allow) - SUM(m.deduc)  as total_suspended_gross 
                         FROM tbl_master m 
                         JOIN master_staff ms ON m.staff_id = ms.staff_id 
                         WHERE m.period = :period AND ms.period = :period2 AND ms.BCODE = '43'";
        $suspendedResult = $app->selectOne($suspendedSql, [':period' => $period, ':period2' => $period]);
        $total_suspended_gross = (float)($suspendedResult['total_suspended_gross'] ?? 0);

        $deductionsSql = "SELECT e.ed_id, e.ed, SUM(m.deduc) as total_amount
                          FROM tbl_master m
                          JOIN tbl_earning_deduction e ON m.allow_id = e.ed_id
                          WHERE m.period = :period AND e.edType = 2 AND e.status = 'Active' AND e.ed_id != 24
                          GROUP BY e.ed_id, e.ed
                          HAVING total_amount > 0
                          ORDER BY e.ed_id ASC";
        
        $deductionsData = $app->selectAll($deductionsSql, [':period' => $period]);

        $main_deductions = [];
        $retained_deductions = [];
        $total_main_deductions = 0;
        $total_retained_deductions = 0;

        $retainedIds = [28, 34, 30];

        foreach ($deductionsData as $d) {
            $amount = (float)$d['total_amount'];
            
            if (in_array((int)$d['ed_id'], $retainedIds)) {
                $retained_deductions[] = [
                    'name' => strtoupper($d['ed']),
                    'amount' => $amount
                ];
                $total_retained_deductions += $amount;
            } else {
                $main_deductions[] = [
                    'name' => strtoupper($d['ed']),
                    'amount' => $amount
                ];
                $total_main_deductions += $amount;
            }
        }

        // Add "SUSPENDED" gross to retained deductions
        if ($total_suspended_gross > 0) {
            $retained_deductions[] = [
                'name' => 'SUSPENDED',
                'amount' => $total_suspended_gross
            ];
        }
        
        $total_retained = $total_retained_deductions + $total_suspended_gross;
        $actual_amount_paid = $gross_after_tax - $total_retained;
        $net_pay = $actual_amount_paid - $total_main_deductions;

        // Get business information
        $businessInfo = $app->getBusinessName();
        $businessNameRaw = $businessInfo['business_name'] ?? '';
        $businessName = str_replace(',', ",\n", (string)$businessNameRaw);

        // Get logged-in user details
        $userDetails = $app->getUsersDetails($_SESSION['SESS_MEMBER_ID']);
        $printedBy = $userDetails['NAME'] ?? 'Administrator';

        // Custom PDF Wrapper
        class MYPDF extends TCPDF {
            private $printedBy = '';
            private $currentDate = '';

            public function setCustomFooterData($printedBy, $currentDate) {
                $this->printedBy = $printedBy !== null ? (string)$printedBy : '';
                $this->currentDate = $currentDate !== null ? (string)$currentDate : '';
            }

            public function Header() {}
            public function Footer() {
                $this->SetY(-15);
                $this->SetFont('helvetica', '', 8);
                $this->Cell(90, 10, 'Date Printed: ' . $this->currentDate, 0, 0, 'L');
                $this->Cell(90, 10, 'Printed By: ' . $this->printedBy, 0, 1, 'R');
            }
        }

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($printedBy);
        $pdf->SetTitle('Subvention Analysis Report');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);

        // Set custom footer data
        $currentDate = date('Y-m-d H:i:s');
        $pdf->setCustomFooterData($printedBy, $currentDate);

        $pdf->SetMargins(15, 20, 20);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->SetFont('helvetica', '', 9);

        // Define generic format for numbers
        function fmt($num) {
            return number_format($num, 2);
        }

        $pdf->AddPage();

        // Header with logos and institution name
        $logoLeft = '../assets/images/ogun_logo.png';
        $logoRight = '../assets/images/tasce_r_logo.png';
        if (file_exists($logoLeft) && file_exists($logoRight)) {
            $pdf->Image($logoLeft, 15, 10, 25, 25, 'PNG', '', 'T', false, 300, '', false, false, 0);
            $pdf->Image($logoRight, 165, 10, 25, 25, 'PNG', '', 'T', false, 300, '', false, false, 0);
        }

        // Institution name and report title
        $pdf->SetY(10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(140, 15, $businessName, 0, 'C', false, 1, 35, null, true, 0, false, true, 15, 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 5, 'ANALYSIS REPORT', 0, 1, 'C');
        $pdf->Cell(0, 5, 'FOR THE MONTH OF ' . strtoupper($period_description), 0, 1, 'C');
        $pdf->Ln(5);

        $formatted_gross = fmt($gross);
        $formatted_tax = fmt($tax);
        $formatted_net = fmt($gross_after_tax);
        $formatted_net_pay = fmt($net_pay);
        $formatted_actual_paid = fmt($actual_amount_paid);

        $pdf->SetFont('helvetica', '', 9);

        $html = <<<EOD
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid black; padding: 4px; }
            .header-title { font-weight: bold; text-align: center; }
            .bold { font-weight: bold; }
            .right { text-align: right; }
            .uppercase { text-transform: uppercase; }
        </style>

        <table>
            <tr>
                <td class="bold uppercase" width="60%">GROSS</td>
                <td width="20%"></td>
                <td class="bold right" width="20%">{$formatted_gross}</td>
            </tr>
            <tr>
                <td class="bold uppercase">TAX</td>
                <td></td>
                <td class="bold right" style="text-decoration: underline;">{$formatted_tax}</td>
            </tr>
            <tr>
                <td class="bold uppercase">GROSS AFTER TAX</td>
                <td></td>
                <td class="bold right" style="border-bottom: 3px double black;">{$formatted_net}</td>
            </tr>
            <tr>
                <td colspan="3" style="border: none; padding: 5px;"></td>
            </tr>
            <tr>
                <td colspan="2" class="bold uppercase">BREAKDOWN ANALYSIS OF GROSS SALARY AFTER TAX</td>
                <td class="bold right">{$formatted_net}</td>
            </tr>
            <tr>
                <td class="bold uppercase">NET PAY</td>
                <td class="bold right" style="background-color: #f9f9f9;">{$formatted_net_pay}</td>
                <td></td>
            </tr>
EOD;

        // Add main deductions
        foreach ($main_deductions as $d) {
            $formatted_amt = fmt($d['amount']);
            $html .= <<<EOD
            <tr>
                <td class="uppercase">{$d['name']}</td>
                <td class="right">{$formatted_amt}</td>
                <td></td>
            </tr>
EOD;
        }

        $formatted_total_ded = fmt($total_main_deductions);
        $html .= <<<EOD
            <tr>
                <td class="bold uppercase">TOTAL DEDUCTION</td>
                <td></td>
                <td class="right bold">{$formatted_total_ded}</td>
            </tr>
            <tr>
                <td colspan="3" style="border: none; padding: 5px;"></td>
            </tr>
            <tr>
                <td class="bold uppercase">ACTUAL AMOUNT THAT WILL BE PAID</td>
                <td></td>
                <td class="bold right">{$formatted_actual_paid}</td>
            </tr>
EOD;

        // Add Retained Deductions
        foreach ($retained_deductions as $d) {
            $formatted_amt = fmt($d['amount']);
            $html .= <<<EOD
            <tr>
                <td class="uppercase">{$d['name']}</td>
                <td class="right">{$formatted_amt}</td>
                <td></td>
            </tr>
EOD;
        }

        $formatted_retained_total = fmt($total_retained);
        $html .= <<<EOD
            <tr>
                <td class="bold uppercase">DEDUCTION RETAINED IN THE SUBVENTION ACCOUNT</td>
                <td class="bold right" style="font-size: 11pt;">{$formatted_retained_total}</td>
                <td></td>
            </tr>
            <tr>
                <td class="bold uppercase">TOTAL GROSS AFTER TAX</td>
                <td></td>
                <td class="bold right" style="border-bottom: 3px double black;">{$formatted_net}</td>
            </tr>
        </table>
EOD;

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('Subvention_Analysis_Report_' . $period_description . '.pdf', 'D');

    } catch (Exception $e) {
        die('Error generating report: ' . $e->getMessage());
    }
} else {
    die('Invalid parameters.');
}
?>
