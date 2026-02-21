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

        $deductionsSql = "SELECT e.ed_id, e.ed, SUM(m.deduc) as total_amount
                          FROM tbl_master m
                          JOIN tbl_earning_deduction e ON m.allow_id = e.ed_id
                          WHERE m.period = :period AND e.edType = 2 AND e.status = 'Active' AND e.ed_id != 24
                          GROUP BY e.ed_id, e.ed
                          HAVING total_amount > 0
                          ORDER BY e.ed_id ASC";
        
        $deductionsData = $app->selectAll($deductionsSql, [':period' => $period]);

        $deductions = [];
        $retained_deductions = [];
        $total_deductions = 0;
        $total_retained = 0;

        $retainedIds = [28, 29, 34, 30, 36];

        foreach ($deductionsData as $d) {
            $amount = (float)$d['total_amount'];
            
            $deductions[] = [
                'name' => strtoupper($d['ed']),
                'amount' => $amount
            ];
            $total_deductions += $amount;

            if (in_array((int)$d['ed_id'], $retainedIds)) {
                $retained_deductions[] = [
                    'name' => strtoupper($d['ed']),
                    'amount' => $amount
                ];
                $total_retained += $amount;
            }
        }
        
        $actual_amount_paid = $gross_after_tax - $total_deductions;

        // Custom PDF Wrapper
        class MYPDF extends TCPDF {
            public function Header() {}
            public function Footer() {}
        }

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['businessname']);
        $pdf->SetTitle('Subvention Analysis Report');
        
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->SetFont('helvetica', '', 9);

        // Define generic format for numbers
        function fmt($num) {
            return number_format($num, 2);
        }

        $pdf->AddPage();

        $businessName = htmlspecialchars(strtoupper($_SESSION['businessname']));

        $formatted_gross = fmt($gross);
        $formatted_tax = fmt($tax);
        $formatted_net = fmt($gross_after_tax);

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
                <td colspan="3" class="header-title">$businessName</td>
            </tr>
            <tr>
                <td colspan="3" class="header-title uppercase">ANALYSIS FOR THE MONTH OF {$period_description}</td>
            </tr>
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
                <td class="bold right">{$formatted_net}</td>
                <td></td>
            </tr>
EOD;

        // Add all deductions
        foreach ($deductions as $d) {
            $formatted_amt = fmt($d['amount']);
            $html .= <<<EOD
            <tr>
                <td class="uppercase">{$d['name']}</td>
                <td class="right">{$formatted_amt}</td>
                <td></td>
            </tr>
EOD;
        }

        $formatted_total_ded = fmt($total_deductions);
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
                <td colspan="2" class="bold uppercase">NET PAY - TOTAL DEDUCTION</td>
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
                <td class="bold uppercase"><br>DEDUCTION RETAINED IN THE SUBVENTION ACCOUNT</td>
                <td class="bold right" style="font-size: 11pt;"><br>{$formatted_retained_total}</td>
                <td class="bold uppercase right" style="font-size: 8pt;">ACTUAL AMOUNT +<br>DEDUCTION<br>RETAINED</td>
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
