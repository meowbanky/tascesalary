<?php
require_once 'App.php';
$App = new App();

try {
    // Check if date range is provided
    $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
    $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;

    if ($startDate && $endDate) {
        // Fetch log entries for the date range
        $stmt = $App->link->prepare("SELECT * FROM operation_logs WHERE DATE(timestamp) BETWEEN :start_date AND :end_date ORDER BY timestamp DESC");
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $logEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo 'Please provide a valid date range.';
        exit;
    }

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}
?>

    <style>
        table {
            width: 70%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .timestamp-heading {
            background-color: #e2e2e2;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="overflow-auto min-w-full bg-white w-screen">
<h2>Log Entries from <?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?></h2>

<?php if ($logEntries) : ?>
    <?php foreach ($logEntries as $logEntry) : ?>
        <?php
        $operation = $logEntry['operation'];
        $id = $logEntry['id'];
        $tableName = $logEntry['table_name'];
        $data = json_decode($logEntry['data'], true);
        $userId = $logEntry['user_id'];
        $timestamp = $logEntry['timestamp'];
        ?>
        <table>
            <tr class="timestamp-heading">
                <td colspan="2"><?php echo htmlspecialchars($timestamp); ?></td>
            </tr>
            <tr>
                <th>Log ID</th>
                <td><?php echo htmlspecialchars($id); ?></td>
            </tr>
            <tr>
                <th>Operation</th>
                <td><?php echo htmlspecialchars($operation); ?></td>
            </tr>
            <tr>
                <th>Table</th>
                <td><?php echo htmlspecialchars($tableName); ?></td>
            </tr>
            <tr>
                <th>User ID</th>
                <td><?php echo htmlspecialchars($userId); ?></td>
            </tr>
            <tr>
                <th>Data</th>
                <td>
                    <?php if (is_array($data)) : ?>
                        <table border='1'>
                            <tr><th>Field</th><th>Value</th></tr>
                            <?php foreach ($data as $key => $value) : ?>
                                <tr>
                                    <?php
                                    switch ($key) {
                                        case ':staff_id':
                                            $value = $App->getStaffName($value);
                                            $value = $value['NAME'];
                                            $key = 'Action performed on:';
                                            break;
                                        case ':editTime':
                                            $key = "Date/Time";
                                            break;
                                        case ':period':
                                            $value = $App->getPeriodDescription($value);
                                            $value = $value['period'];
                                            $key = "period";
                                            break;
                                        case ':allow_id':
                                            $value = $App->getAllowanceDescription($value);
                                            $value = $value['ed'];
                                            $key = "Allow/Deduc. changed";
                                            break;
                                        case ':value':
                                            $key = "Amount";
                                            break;
                                        case ':userID':
                                        case ':inserted_by':
                                            $value = $App->getStaffName($value);
                                            $value = $value['NAME'];
                                            $key = 'Action Done by:';
                                            break;
                                    }
                                    ?>

                                    <td><?php echo htmlspecialchars($key); ?></td>
                                    <td><?php if($value === NULL) {$value = '';} echo htmlspecialchars($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else : ?>
                        No data available.
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <br>
    <?php endforeach; ?>
<?php else : ?>
    <p>No log entries found for the specified date range.</p>
<?php endif; ?>
</div>
