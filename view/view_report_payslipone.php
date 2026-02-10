<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$selectPeriods = $App->selectDrop("SELECT concat(payperiods.description,'-',payperiods.periodYear) as period, 
                                        payperiods.periodId FROM payperiods ORDER BY periodId DESC ");

$banks = $App->selectDrop("SELECT BANK_ID,BNAME FROM tbl_bank");
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8 px-4">
        <div class="flex justify-center md:justify-end px-4">
            <button id="download-excel-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-download"></i> Download Excel
            </button>
        </div>

        <div class="container mx-auto p-4">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/ogun_logo.png" alt="Logo" class="w-16 h-16 md:w-32 md:h-32">
                        <h1 class="text-center text-xs md:text-xl font-bold"><?php echo $_SESSION['businessname']; ?></h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16 md:w-32 md:h-32">
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
                <div class="mb-4">
                    <label for="ogno" class="block text-sm font-medium text-gray-700">Staff ID</label>
                    <input type="text" id="ogno" name="ogno" class="border border-gray-300 rounded-md p-2 w-full">
                    <input type="hidden" id="staff_id" name="staff_id">
                </div>

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name" class="border border-gray-300 rounded-md p-2 w-full" readonly>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="border border-gray-300 rounded-md p-2 w-full">
                </div>

                <div class="flex justify-start mb-4 gap-2">
                    <button id="submit" type="button" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">Submit</button>

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
            const payslipData = $('#payslipModal .modal-content').html();
            if($('#email').val() == '') {
                const email = prompt("Please enter the employee's email address:");
            }else {
                email = $('#email').val()
            }

            if (email) {

                document.getElementById("backdrop").style.display = "flex";
                $.ajax({
                    url: 'libs/send_payslip.php',
                    type: 'POST',
                    data: { payslip: payslipData,
                        email: email ,
                        staff_id: $('#staff_id').val(),
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
            }
        });

        $('#submit').click(function(event) {
            document.getElementById("backdrop").style.display = "flex";
            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'libs/report_payslipone.php',
                data: {
                    period: $('#pay_period').val(),
                    staff_id: $('#staff_id').val(),
                    email: $('#email').val()
                },
                success: function(response) {
                    $('#table').html(response);
                    $('#backdrop').hide();
                },
                error: function(xhr, status, error) {
                    console.log(error);
                    $('#backdrop').hide();
                }
            });
        });

        $('#print-payslip').click(function() {
            window.print();
        });
    });
</script>
