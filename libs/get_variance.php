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
    COALESCE(t2.gross_salary, 0) - COALESCE(t1.gross_salary, 0) AS salary_difference
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

UNION

SELECT
    COALESCE(t1.staff_id, t2.staff_id) AS staff_id,
    COALESCE(t1.OGNO, t2.OGNO) AS OGNO,
    COALESCE(t1.name, t2.name) AS name,
    COALESCE(t1.gross_salary, 0) AS gross_salary_month1,
    COALESCE(t2.gross_salary, 0) AS gross_salary_month2,
    COALESCE(t2.gross_salary, 0) - COALESCE(t1.gross_salary, 0) AS salary_difference
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
    t1.staff_id = t2.staff_id;
");
            $stmt2->execute([':month2' => $month2,':month1' => $month1 ]);
            $salaries2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Calculate the variance
            $data = [];
                foreach ($salaries2 as $salary2) {
                        $data[] = [
                            'staff_id' => $salary2['OGNO'],
                            'name' => $salary2['name'],
                            'month1_gross' => $salary2['gross_salary_month1'],
                            'month2_gross' => $salary2['gross_salary_month2'],
                            'difference' => $salary2['salary_difference'],
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
