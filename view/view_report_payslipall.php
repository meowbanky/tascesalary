<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$selectPeriods = $App->selectDrop("SELECT concat(payperiods.description,'-',payperiods.periodYear) as period, 
                                        payperiods.periodId FROM payperiods ORDER BY periodId DESC ");

$banks = $App->selectDrop("SELECT BANK_ID,BNAME FROM tbl_bank");
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
            <h1 class="text-2xl font-bold mb-6"></h1>
            <button id="download-excel-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-download"></i> Download Excel
            </button>
        </div>

        <div class="container mx-auto p-4">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/ogun_logo.png" alt="Logo" class="w-16 h-16">
                        <h1 class=" text-center text-xl font-bold"><?php echo $_SESSION['businessname']; ?></h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="pay_period" class="block text-sm font-medium text-gray-700">Pay Period :</label>
                    <select id="pay_period" name="pay_period" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Item</option>
                        <?php
                        foreach ($selectPeriods as $selectPeriod) {
                            echo '<option ' . ($_SESSION["currentactiveperiod"] == $selectPeriod['periodId'] ? "selected" : "") . ' value="' . $selectPeriod['periodId'] . '">' . $selectPeriod['period'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="flex justify-start mb-4 gap-2">
                    <button id="emailButton" type="button" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">@Send mail</button>
                </div>

                <div id="table"></div>
            </div>
        </div>
    </div>
</div>
<div class="backdrop" id="backdrop">
    <div class="spinner"></div>
</div>
<script>
    $(document).ready(function() {
        $('#emailButton').on('click', function() {

                document.getElementById("backdrop").style.display = "flex";
                $.ajax({
                    url: 'libs/send_payslip_all.php',
                    type: 'POST',
                    data: {
                        period: $('#pay_period').val()}
                    ,
                    success: function(response) {
                        $('#backdrop').hide(); // Hide the backdrop and spinner
                        alert('Payslip emailed successfully.');
                    },
                    error: function(xhr, status, error) {
                        $('#backdrop').hide(); // Hide the backdrop and spinner
                        alert('Error sending email: ' + error);
                    }
                });

        });

        $('#print-payslip').click(function() {
            window.print();
        });
    });
</script>
