<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
    $businessName = str_replace(',', ', ', $businessInfo['business_name']);

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

    // Initialize PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Variance Report');

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(20); // Staff ID
    $sheet->getColumnDimension('B')->setWidth(40); // Name
    $sheet->getColumnDimension('C')->setWidth(25); // Month 1 Gross Salary
    $sheet->getColumnDimension('D')->setWidth(25); // Month 2 Gross Salary
    $sheet->getColumnDimension('E')->setWidth(25); // Difference
    $sheet->getColumnDimension('F')->setWidth(50); // Remark

    // Header
    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A1', $businessName);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:F2');
    $sheet->setCellValue('A2', 'VARIANCE REPORT');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:F3');
    $sheet->setCellValue('A3', 'Period: ' . $month1Desc . ' vs ' . $month2Desc);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Table header
    $headerRow = 4;
    $headers = ['Staff ID', 'Name', $month1Desc, $month2Desc, 'Difference', 'Remark'];
    $sheet->fromArray($headers, NULL, 'A' . $headerRow);
    $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
    $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Table data
    $row = $headerRow + 1;
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $item['staff_id'] ?? '');
        $sheet->setCellValue('B' . $row, $item['name'] ?? '');
        $sheet->setCellValue('C' . $row, $item['month1_gross']);
        $sheet->setCellValue('D' . $row, $item['month2_gross']);
        $sheet->setCellValue('E' . $row, $item['difference']);
        $sheet->setCellValue('F' . $row, $item['remark'] ?? '');

        // Apply text wrapping
        $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);

        // Apply number formatting
        $sheet->getStyle('C' . $row . ':E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;
    }

    // Totals
    $sheet->setCellValue('A' . $row, '');
    $sheet->setCellValue('B' . $row, 'Difference Total');
    $sheet->setCellValue('C' . $row, '');
    $sheet->setCellValue('D' . $row, '');
    $sheet->setCellValue('E' . $row, $totalDifference);
    $sheet->setCellValue('F' . $row, '');
    $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Output the Excel file
    $filename = 'Variance_Report_' . str_replace(' ', '_', $month1Desc) . '_vs_' . str_replace(' ', '_', $month2Desc) . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
} else {
    echo 'Invalid request. Please provide valid months.';
}
?>