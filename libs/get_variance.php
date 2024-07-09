<?php
require_once 'App.php';
$App = new App;

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $month1 = filter_input(INPUT_POST, 'month1', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $month2 = filter_input(INPUT_POST, 'month2', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($month1 && $month2) {
        try {
            // Fetch data for the first month
            $stmt1 = $App->link->prepare("SELECT
                    sum(tbl_master.allow) as gross_salary, 
                    tbl_master.staff_id, 
                    employee.`NAME` as name
                FROM
                    tbl_master
                    INNER JOIN
                    employee
                    ON 
                        tbl_master.staff_id = employee.staff_id
                        WHERE period = :month1
                    GROUP BY staff_id" );
            $stmt1->execute([':month1' => $month1]);
            $salaries1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

            // Fetch data for the second month
            $stmt2 = $App->link->prepare("SELECT
                    sum(tbl_master.allow) as gross_salary, 
                    tbl_master.staff_id, 
                    employee.`NAME` as name
                FROM
                    tbl_master
                    INNER JOIN
                    employee
                    ON 
                        tbl_master.staff_id = employee.staff_id
                        WHERE period = :month2
                    GROUP BY staff_id");
            $stmt2->execute([':month2' => $month2]);
            $salaries2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Calculate the variance
            $data = [];
            foreach ($salaries1 as $salary1) {
                foreach ($salaries2 as $salary2) {
                    if ($salary1['staff_id'] == $salary2['staff_id']) {
                        $data[] = [
                            'staff_id' => $salary1['staff_id'],
                            'name' => $salary1['name'],
                            'month1_gross' => $salary1['gross_salary'],
                            'month2_gross' => $salary2['gross_salary'],
                            'difference' => $salary2['gross_salary'] - $salary1['gross_salary']
                        ];
                    }
                }
            }

            $response['status'] = 'success';
            $response['data'] = $data;
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
