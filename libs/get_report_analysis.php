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
                         INNER JOIN master_staff ms ON m.staff_id = ms.staff_id 
                         WHERE m.period = :period AND ms.period = :period2 AND ms.BCODE = '43'";
        $suspendedResult = $app->selectOne($suspendedSql, [':period' => $period, ':period2' => $period]);
        $total_suspended_gross = (float)($suspendedResult['total_suspended_gross'] ?? 0);

        // Query to get all active deductions for this period, excluding TAX (ed_id = 24)
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

        // Specific IDs to be listed in the bottom "Retained" section
        $retainedIds = [
            28, // COLL. FEE
            34, // SALARY ADV.
            30  // RENT
        ];

        foreach ($deductionsData as $d) {
            $amount = (float)$d['total_amount'];
            
            if (in_array((int)$d['ed_id'], $retainedIds)) {
                $retained_deductions[] = [
                    'id' => $d['ed_id'],
                    'name' => $d['ed'],
                    'amount' => $amount
                ];
                $total_retained_deductions += $amount;
            } else {
                $main_deductions[] = [
                    'id' => $d['ed_id'],
                    'name' => $d['ed'],
                    'amount' => $amount
                ];
                $total_main_deductions += $amount;
            }
        }

        // Add "SUSPENDED" gross to retained deductions
        if ($total_suspended_gross > 0) {
            $retained_deductions[] = [
                'id' => 'suspended',
                'name' => 'SUSPENDED',
                'amount' => $total_suspended_gross
            ];
        }

        $total_retained = $total_retained_deductions + $total_suspended_gross;
        $actual_amount_paid = $gross_after_tax - $total_retained;
        $net_pay = $actual_amount_paid - $total_main_deductions;

        $response = [
            'status' => 'success',
            'data' => [
                'period_description' => $period_description,
                'gross' => $gross,
                'tax' => $tax,
                'gross_after_tax' => $gross_after_tax,
                'net_pay' => $net_pay,
                'actual_amount_paid' => $actual_amount_paid,
                'deductions' => $main_deductions,
                'total_deductions' => $total_main_deductions,
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
