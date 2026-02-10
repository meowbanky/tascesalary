<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $salarytype = $_POST['salaryType'];
    $allowcode = $_POST['allowance'];
    $grade = $_POST['level'];
    $salaryTables = $App->getSalaryTable($salarytype, $allowcode, $grade);
}
?>

<div class="overflow-x-auto">
    <table id="salary-table" class="min-w-full bg-white">
        <thead>
        <tr class="w-full bg-gray-800 text-white">
            <th class="py-2 px-4 border border-gray-300">ID</th>
            <th class="py-2 px-4 border border-gray-300">Allow</th>
            <th class="py-2 px-4 border border-gray-300">Allow Id</th>
            <th class="py-2 px-4 border border-gray-300">Grade</th>
            <th class="py-2 px-4 border border-gray-300">Step</th>
            <th class="py-2 px-4 border border-gray-300">Value</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($salaryTables) { ?>
            <?php foreach ($salaryTables as $salaryTable) { ?>
                <tr>
                    <td class="border px-4 py-2"><?php echo $salaryTable['allow_id']; ?></td>
                    <td class="border px-4 py-2"><?php echo $salaryTable['ed']; ?></td>
                    <td class="border px-4 py-2"><?php echo $salaryTable['allowcode']; ?></td>
                    <td class="border px-4 py-2"><?php echo $salaryTable['grade']; ?></td>
                    <td class="border px-4 py-2"><?php echo $salaryTable['step']; ?></td>
                    <td class="border px-4 py-2"><?php echo $salaryTable['value']; ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        function initializeTabledit() {
            $('#salary-table').Tabledit({
                url: 'libs/edit_salarytable.php',
                columns: {
                    identifier: [0, 'ID'],
                    editable: [[5, 'value']]
                },
                eventType: 'dblclick',
                editButton: false,
                hideIdentifier: true,
                onSuccess: function(data, textStatus, jqXHR) {
                    console.log('Edit successful');
                },
                onFail: function(jqXHR, textStatus, errorThrown) {
                    console.log('Edit failed: ' + textStatus);
                }
            });
        }

        // Initialize Tabledit on the existing table if it already contains data
        if ($('#salary-table tbody tr').length > 0) {
            initializeTabledit();
        }
    });
</script>
