<?php
require_once 'App.php';
$App = new App();

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['type'] == 'gender') {
        $genders = $App->getGender();
        $labels = [];
        $series = [];

        foreach ($genders as $gender) {
            $labels[] = $gender['labels'];
            $series[] = $gender['series'];
        }

        echo json_encode(['labels' => $labels, 'series' => $series]);
    }

    if ($_GET['type'] == 'dept') {
        $depts = $App->getDept();
        $labels = [];
        $series = [];

        foreach ($depts as $dept) {
            $labels[] = $dept['labels'];
            $series[] = $dept['series'];
        }

        echo json_encode(['labels' => $labels, 'series' => $series]);
    }
    if ($_GET['type'] == 'finance') {
        $data = $App->getFinance();
        $labels = [];
        $allowances = [];
        $deductions = [];
        foreach ($data as $row) {
            $labels[] = $row['month'];
            $allowances[] = (float) $row['total_allowance'];
            $deductions[] = (float) $row['total_deduction'];
        }

        // Return the data as JSON
        echo json_encode(['labels' => $labels, 'allowances' => $allowances, 'deductions' => $deductions]);

    }
}

?>