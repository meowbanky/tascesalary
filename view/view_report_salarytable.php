<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$allowances = $App->selectDrop("SELECT edDesc, ed_id FROM tbl_earning_deduction WHERE type  = 1");

$salaryTypes = $App->selectDrop("SELECT
	tbl_salaryType.salaryType_id,
	tbl_salaryType.SalaryType 
FROM
	tbl_salaryType");

?>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #payslipModal, #payslipModal * {
            visibility: visible;
        }
        #payslipModal {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }

</style>
<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
            <h1 class="text-2xl font-bold mb-6">Salary Table</h1>
            <div>
                <button id="download-excel-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-download"></i> Download Excel
                </button>
            </div>
        </div>

        <div class="container mx-auto p-4">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/ogun_logo.png" alt="Logo" class="w-16 h-16">
                        <h1 class="text-2xl font-bold"><?php echo $_SESSION['businessname']; ?></h1>
                    </div>

                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16">
                    </div>
                </div>
                <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
                    <h1 class="text-2xl font-bold mb-6 text-center">Salary Table</h1>
                    <form id="salaryTableForm" method="POST">
                        <div class="mb-4">
                            <label for="salaryType" class="block text-sm font-medium text-gray-700">Salary Types :</label>
                            <select id="salaryType" name="salaryType" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                                <option value="">Select salary Type</option>
                                <?php
                                if($salaryTypes) {
                                    foreach ($salaryTypes as $salaryType) {
                                        echo '<option value="' . $salaryType['salaryType_id'] . '">' . $salaryType['SalaryType'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="allowance" class="block text-sm font-medium text-gray-700">Allowance :</label>
                            <select id="allowance" name="allowance" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                                <option value="">Select Allowance</option>
                                <?php
                                if($allowances) {
                                    foreach ($allowances as $allowance) {
                                        echo '<option value="' . $allowance['ed_id'] . '">' . $allowance['edDesc'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="level" class="block text-sm font-medium text-gray-700">Level :</label>
                            <select id="level" name="level" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                                <option value="">Select level</option>
                                <?php
                                for ($i = 1; $i <= 15; $i++) {
                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <div class="flex justify-start m-2">
                                <button type="submit" id="compareButton" class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="result2" class="overflow-x-auto font-xs hidden">
                    <h2 class="text-xl font-bold mb-4 text-center">Variance Result</h2>
                    <table id="salary-table" class="min-w-full bg-white border border-gray-200">
                        <thead>
                        <tr class="w-full bg-gray-800 text-white">
                            <th class="py-2 px-4 border border-gray-300">Allow</th>
                            <th class="py-2 px-4 border border-gray-300">Allow Id</th>
                            <th class="py-2 px-4 border border-gray-300">Grade</th>
                            <th class="py-2 px-4 border border-gray-300">Step</th>
                            <th class="py-2 px-4 border border-gray-300">Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="border px-4 py-2"></td>
                            <td class="border px-4 py-2"></td>
                            <td class="border px-4 py-2"></td>
                            <td class="border px-4 py-2"></td>
                            <td class="border px-4 py-2"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        function setModalMaxHeight() {
            var screenHeight = window.innerHeight;
            var modalMaxHeight = screenHeight - 60; // Subtract 60px from screen height
            $('.modal-content').css('max-height', 70 + 'vh');
        }

        $('#salaryTableForm').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: 'libs/get_report_salarytable.php',
                data: formData,
                success: function(response) {
                    $('#result2').removeClass('hidden');
                    $('#result2').html(response);
                    // Reinitialize Tabledit after loading the table
                    initializeTabledit();
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        });

        function initializeTabledit() {
            $('#salary-table').Tabledit({
                url: 'libs/edit_salarytable.php',
                columns: {
                    identifier: [1, 'allow_id'],
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
