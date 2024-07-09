<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();


$selectPeriods = $App->selectDrop("SELECT concat(payperiods.description,'-',payperiods.periodYear) as period, 
	                                    payperiods.periodId FROM payperiods ORDER BY periodId DESC ");

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
        <h1 class="text-2xl font-bold mb-6">Variance</h1>
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
                        <h1 class="text-2xl font-bold"<? echo  $_SESSION['businessname']; ?></h1>
                    </div>

                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16">
                    </div>
                </div>
                <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
                    <h1 class="text-2xl font-bold mb-6 text-center">Compare Gross Salary Between Months</h1>
                    <form id="varianceForm" method="POST">
                <div class="mb-4">
                    <label for="month1" class="block text-sm font-medium text-gray-700">Current Month :</label>
                    <select id="month1" name="month1" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Month 1</option>
                        <?php
                        foreach ($selectPeriods as $selectPeriod) {
                            echo '<option value="' . $selectPeriod['periodId'] . '">' . $selectPeriod['period'] . '</option>';
                        }
                        ?>
                    </select>

                </div>
                <div class="mb-4">
                    <label for="month2" class="block text-sm font-medium text-gray-700">Previous Month :</label>
                    <select id="month2" name="month2" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Month 2</option>
                        <?php
                        foreach ($selectPeriods as $selectPeriod) {
                            echo '<option value="' . $selectPeriod['periodId'] . '">' . $selectPeriod['period'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <div class="flex justify-start m-2">
                        <button type="submit" id="compareButton" class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">
                            Compare
                        </button>
                    </div>
                </div>
                    </form>
                </div>
                <div id="result2" class="overflow-x-auto font-xs hidden">
                    <h2 class="text-xl font-bold mb-4 text-center">Variance Result</h2>
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                        <tr class="w-full bg-gray-800 text-white">
                            <th class="py-2 px-4 border border-gray-300">Staff ID</th>
                            <th class="py-2 px-4 border border-gray-300">Name</th>
                            <th class="py-2 px-4 border border-gray-300">Month 1 Gross Salary</th>
                            <th class="py-2 px-4 border border-gray-300">Month 2 Gross Salary</th>
                            <th class="py-2 px-4 border border-gray-300">Difference</th>
                        </tr>
                        </thead>
                        <tbody id="resultBody">
                        <!-- Results will be inserted here by JavaScript -->
                        </tbody>
                    </table>

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
            $('#download-excel-button').click(function() {
                var period = $('#pay_period').val();
                window.location.href = 'libs/generate_excel_payrollsummary.php?period='+period;
            });

            $('#varianceForm').submit(function(event) {
                event.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: 'libs/get_variance.php',
                    dataType: 'json',
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            var grandtotal =0;
                            $('#resultBody').empty();
                            response.data.forEach(function(item) {
                                var row = `<tr class="bg-gray-50">
                                    <td class="py-2 px-4 border border-gray-300">${item.staff_id}</td>
                                    <td class="py-2 px-4 border border-gray-300">${item.name}</td>
                                    <td class="py-2 px-4 border border-gray-300">${item.month1_gross}</td>
                                    <td class="py-2 px-4 border border-gray-300">${item.month2_gross}</td>
                                    <td class="py-2 px-4 border border-gray-300">${item.difference}</td>
                                </tr>`;
                                $('#resultBody').append(row);
                                grandtotal+=item.difference;
                            });

                                var row2 = `
                                <tr class="bg-gray-50">
                                    <td colspan="4" class="py-2 px-4 border border-gray-300 font-bold">Difference Total</td>
                                    <td class="py-2 px-4 border border-gray-300 font-bold">${grandtotal}</td>
                                </tr>
                                `;

                                $('#resultBody').append(row2);

                                $('#result2').removeClass('hidden');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });
            });

        });

</script>

