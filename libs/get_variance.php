<?php
require_once 'App.php';
$App = new App;

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $month1 = filter_input(INPUT_POST, 'month1', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $month2 = filter_input(INPUT_POST, 'month2', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$month1_description = $App->getPeriodDescription($month1);
$month2_description = $App->getPeriodDescription($month2);
$month1_description = $month1_description['period'];
$month2_description = $month2_description['period'];
    if ($month1 && $month2) {
        try {
            $stmt2 = $App->link->prepare("SELECT
    COALESCE(t1.staff_id, t2.staff_id) AS staff_id,
    COALESCE(t1.OGNO, t2.OGNO) AS OGNO,
    COALESCE(t1.name, t2.name) AS name,
    COALESCE(t1.gross_salary, 0) AS gross_salary_month1,
    COALESCE(t2.gross_salary, 0) AS gross_salary_month2,
    COALESCE(t2.gross_salary, 0) - COALESCE(t1.gross_salary, 0) AS salary_difference,
    vr.remark,
    t1.grade_level AS month1_grade,
    t2.grade_level AS month2_grade,
    t1.step AS month1_step,
    t2.step AS month2_step,
    t1.allowances AS month1_allowances,
    t2.allowances AS month2_allowances
FROM
    (SELECT
        sum(tbl_master.allow) AS gross_salary, 
        tbl_master.staff_id,
        employee.OGNO,
        employee.`NAME` AS name,
        employee.GRADE AS grade_level,
        employee.STEP AS step,
        GROUP_CONCAT(CONCAT(ed.edDesc, ':', tbl_master.allow) ORDER BY ed.edDesc SEPARATOR '; ') AS allowances
    FROM
        tbl_master
    INNER JOIN
        employee ON tbl_master.staff_id = employee.staff_id
    LEFT JOIN
        tbl_earning_deduction ed ON tbl_master.allow_id = ed.ed_id
    WHERE
        period = :month1
    GROUP BY
        tbl_master.staff_id) t1
LEFT JOIN
    (SELECT
        sum(tbl_master.allow) AS gross_salary, 
        tbl_master.staff_id,
        employee.OGNO,
        employee.`NAME` AS name,
        employee.GRADE AS grade_level,
        employee.STEP AS step,
        GROUP_CONCAT(CONCAT(ed.edDesc, ':', tbl_master.allow) ORDER BY ed.edDesc SEPARATOR '; ') AS allowances
    FROM
        tbl_master
    INNER JOIN
        employee ON tbl_master.staff_id = employee.staff_id
    LEFT JOIN
        tbl_earning_deduction ed ON tbl_master.allow_id = ed.ed_id
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
    vr.remark,
    t1.grade_level AS month1_grade,
    t2.grade_level AS month2_grade,
    t1.step AS month1_step,
    t2.step AS month2_step,
    t1.allowances AS month1_allowances,
    t2.allowances AS month2_allowances
FROM
    (SELECT
        sum(tbl_master.allow) AS gross_salary, 
        tbl_master.staff_id,
        employee.OGNO,
        employee.`NAME` AS name,
        employee.GRADE AS grade_level,
        employee.STEP AS step,
        GROUP_CONCAT(CONCAT(ed.edDesc, ':', tbl_master.allow) ORDER BY ed.edDesc SEPARATOR '; ') AS allowances
    FROM
        tbl_master
    INNER JOIN
        employee ON tbl_master.staff_id = employee.staff_id
    LEFT JOIN
        tbl_earning_deduction ed ON tbl_master.allow_id = ed.ed_id
    WHERE
        period = :month1
    GROUP BY
        tbl_master.staff_id) t1
RIGHT JOIN
    (SELECT
        sum(tbl_master.allow) AS gross_salary, 
        tbl_master.staff_id,
        employee.OGNO,
        employee.`NAME` AS name,
        employee.GRADE AS grade_level,
        employee.STEP AS step,
        GROUP_CONCAT(CONCAT(ed.edDesc, ':', tbl_master.allow) ORDER BY ed.edDesc SEPARATOR '; ') AS allowances
    FROM
        tbl_master
    INNER JOIN
        employee ON tbl_master.staff_id = employee.staff_id
    LEFT JOIN
        tbl_earning_deduction ed ON tbl_master.allow_id = ed.ed_id
    WHERE
        period = :month2
    GROUP BY
        tbl_master.staff_id) t2
ON
    t1.staff_id = t2.staff_id
LEFT JOIN
    variance_remarks vr ON t2.staff_id = vr.staff_id 
    AND vr.month1_period_id = :month1 
    AND vr.month2_period_id = :month2;
");
            $stmt2->execute([':month2' => $month2,':month1' => $month1 ]);
            $salaries2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Helper function to parse allowances
            if (!function_exists('parseAllowances')) {
                function parseAllowances($allowanceString) {
                    if (empty($allowanceString)) return [];
                    $allowances = [];
                    $items = explode('; ', $allowanceString);
                    foreach ($items as $item) {
                        if (strpos($item, ':') !== false) {
                            list($desc, $amount) = explode(':', $item, 2);
                            $allowances[trim($desc)] = floatval($amount);
                        }
                    }
                    return $allowances;
                }
            }
            
            // Helper function to compare allowances
            if (!function_exists('compareAllowances')) {
                function compareAllowances($month1, $month2) {
                    $changes = [];
                    $allKeys = array_unique(array_merge(array_keys($month1), array_keys($month2)));
                    
                    foreach ($allKeys as $key) {
                        $val1 = $month1[$key] ?? 0;
                        $val2 = $month2[$key] ?? 0;
                        $diff = $val2 - $val1;
                        
                        if (abs($diff) > 0.01) {
                            if ($val1 == 0) {
                                $changes[] = "$key added";
                            } elseif ($val2 == 0) {
                                $changes[] = "$key removed";
                            } else {
                                $sign = $diff > 0 ? '+' : '';
                                $changes[] = "$key $sign" . number_format($diff, 2);
                            }
                        }
                    }
                    
                    return $changes;
                }
            }

            // Calculate the variance and auto-generate remarks
            $data = [];
                foreach ($salaries2 as $salary2) {
                        // Auto-generate remark if none exists
                        $autoRemark = '';
                        if (empty($salary2['remark'])) {
                            $remarks = [];
                            
                            // Check grade change
                            if ($salary2['month1_grade'] != $salary2['month2_grade']) {
                                $remarks[] = "Grade: {$salary2['month1_grade']} → {$salary2['month2_grade']}";
                            }
                            
                            // Check step change
                            if ($salary2['month1_step'] != $salary2['month2_step']) {
                                $remarks[] = "Step: {$salary2['month1_step']} → {$salary2['month2_step']}";
                            }
                            
                            // Analyze allowance changes
                            $month1Allowances = parseAllowances($salary2['month1_allowances']);
                            $month2Allowances = parseAllowances($salary2['month2_allowances']);
                            $allowanceChanges = compareAllowances($month1Allowances, $month2Allowances);
                            
                            if (!empty($allowanceChanges)) {
                                $remarks[] = "Allowances: " . implode(', ', $allowanceChanges);
                            }
                            
                            // If no specific changes but there's a difference in gross
                            if (empty($remarks) && abs($salary2['salary_difference']) > 0.01) {
                                $remarks[] = "Gross salary variance with no structural changes";
                            }
                            
                            $autoRemark = !empty($remarks) ? implode('; ', $remarks) : '';
                        }
                        
                        $data[] = [
                            'staff_id' => $salary2['OGNO'],
                            'name' => $salary2['name'],
                            'month1_gross' => $salary2['gross_salary_month1'],
                            'month2_gross' => $salary2['gross_salary_month2'],
                            'difference' => $salary2['salary_difference'],
                            'remark' => $salary2['remark'] ?? $autoRemark,
                        ];
                    }


            $response['status'] = 'success';
            $response['data'] = $data;
            $response['month1_description'] = $month1_description;
            $response['month2_description'] = $month2_description;
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid input';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
