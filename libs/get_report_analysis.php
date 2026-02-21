<?php
session_start();
require_once 'App.php';
$app = new App();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['period'])) {
    $period = $_POST['period'];
    
    // Get Period Description
    $periodDescSql = "SELECT concat(description,'-',periodYear) as period FROM payperiods WHERE periodId = :period";
    $periodDescStmt = $app->selectOne($periodDescSql, [':period' => $period]);
    $period_description = $periodDescStmt ? $periodDescStmt['period'] : 'Unknown';

    try {
        // Query to get overall Gross and Tax for the period
        // tbl_master.allow represents the gross earnings
        // tbl_master.deduc where allow_id = 24 represents TAX
        $summarySql = "SELECT 
                        SUM(allow) AS total_gross,
                        (SELECT SUM(deduc) FROM tbl_master WHERE period = :period AND allow_id = 24) AS total_tax
                       FROM tbl_master 
                       WHERE period = :period";
        $summary = $app->selectOne($summarySql, [':period' => $period]);
        
        $gross = (float)($summary['total_gross'] ?? 0);
        $tax = (float)($summary['total_tax'] ?? 0);
        $gross_after_tax = $gross - $tax;

        // Query to get all active deductions for this period
        // Exclude TAX (ed_id = 24) since it's already accounted for
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

        // The specific IDs to be listed in the bottom "Retained" section based on the user's report mockup
        $retainedIds = [
            28, // COLL. FEE
            29, // SCH FEES NEW
            34, // SALARY ADV.
            30, // RENT
            36  // NASU LOAN NEW
        ];

        foreach ($deductionsData as $d) {
            $amount = (float)$d['total_amount'];
            
            // All deductions are added to the general deduction list (including retained ones)
            $deductions[] = [
                'id' => $d['ed_id'],
                'name' => $d['ed'],
                'amount' => $amount
            ];
            $total_deductions += $amount;

            // Furthermore, the retained ones are copied into the retained list
            if (in_array((int)$d['ed_id'], $retainedIds)) {
                $retained_deductions[] = [
                    'id' => $d['ed_id'],
                    'name' => $d['ed'],
                    'amount' => $amount
                ];
                $total_retained += $amount;
            }
        }

        $actual_amount_paid = $gross_after_tax - $total_deductions;

        $response = [
            'status' => 'success',
            'data' => [
                'period_description' => $period_description,
                'gross' => $gross,
                'tax' => $tax,
                'gross_after_tax' => $gross_after_tax,
                'actual_amount_paid' => $actual_amount_paid,
                'deductions' => $deductions,
                'total_deductions' => $total_deductions,
                'retained_deductions' => $retained_deductions,
                'total_retained' => $total_retained
            ]
        ];

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
