<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';

// Your query to fetch log data based on date range
$query = "SELECT * FROM operation_logs WHERE timestamp BETWEEN :startDate AND :endDate";
$stmt = $App->link->prepare($query);
$stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($logs) {
    echo "<table class='min-w-full bg-white border border-gray-200'>";
    echo "<thead>";
    echo "<tr class='w-full bg-gray-800 text-white'>";

    // Dynamically generate table headers based on the first log entry
    $headers = array_keys($logs[0]);
    foreach ($headers as $header) {
        echo "<th class='py-2 px-4 border border-gray-300'>".htmlspecialchars($header)."</th>";
    }

    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    foreach ($logs as $log) {
        echo "<tr>";
        foreach ($headers as $header) {
            // Ensure the key exists in the log entry
            $value = $log[$header] ?? '';
            if (strpos($header, 'json_column_name') !== false) { // Replace 'json_column_name' with the actual column name that contains JSON data
                $decodedValue = json_decode(html_entity_decode($value, ENT_QUOTES), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $formattedValue = '<ul>';
                    foreach ($decodedValue as $key => $val) {
                        $formattedValue .= '<li><strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($val) . '</li>';
                    }
                    $formattedValue .= '</ul>';
                    $value = $formattedValue;
                } else {
                    $value = htmlspecialchars($value); // If JSON decoding fails, display the raw value
                }
            } else {
                $value = htmlspecialchars($value);
            }
            echo "<td class='py-2 px-4 border border-gray-300'>".$value."</td>";
        }
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p>No logs found for the selected date range.</p>";
}
?>
