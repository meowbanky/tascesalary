<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$selectPeriods = $App->selectDrop("SELECT concat(payperiods.description,'-',payperiods.periodYear) as period, 
	                                    payperiods.periodId FROM payperiods ORDER BY periodId DESC ");

$lists = $App->selectDrop("SELECT tbl_earning_deduction.ed_id, tbl_earning_deduction.edType,tbl_earning_deduction.ed FROM tbl_earning_deduction");


?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
            <h1 class="text-2xl font-bold mb-6 print:!block">Allowance/Deduction List</h1>
            <div>
                <button id="export-pdf-button"
                    class="ml-2 mb-2 px-4 py-2 bg-orange-500 text-white rounded-md shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button id="download-excel-button"
                    class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-download"></i> Download Excel
                </button>
            </div>
        </div>
        <div class="container mx-auto p-4">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <!-- Left Logo and Business Name -->
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/ogun_logo.png" alt="Logo" class="w-16 h-16">
                    </div>

                    <!-- Centered Texts -->
                    <div class="flex flex-col items-center">
                        <h1 class="text-2xl font-bold text-center"><?php echo $_SESSION['businessname']; ?></h1>
                        <h2 class="text-lg font-semibold">Allowance/Deduction List</h2>
                    </div>

                    <!-- Right Logo -->
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16 print">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="pay_period" class="block text-sm font-medium text-gray-700">Pay Period:</label>
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
                    <label for="pay_list" class="block text-sm font-medium text-gray-700">List:</label>
                    <select id="pay_list" name="pay_list" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Item</option>
                        <?php
                        foreach ($lists as $list) {
                            echo "<option data-type='{$list['edType']}' value='" . $list['ed_id'] . "'>" . $list['ed'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email (Optional):</label>
                    <input type="email" id="email" name="email" class="border border-gray-300 rounded-md p-2 w-full"
                        placeholder="Enter email to send report (leave blank to download)">
                </div>
                <div class="mb-4">
                    <div class="flex justify-start m-2 gap-2">
                        <button id="submit" type="button"
                            class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">Submit</button>
                    </div>
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
    function setModalMaxHeight() {
        var screenHeight = window.innerHeight;
        var modalMaxHeight = screenHeight - 60;
        $('.modal-content').css('max-height', '70vh');
    }

    function validateEmail(email) {
        if (!email) return true; // Empty email is valid (will trigger download)
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function validateInputs() {
        var period = $('#pay_period').val();
        var list = $('#pay_list').val();
        var email = $('#email').val();
        if (!period) {
            alert('Please select a pay period.');
            $('#backdrop').hide();
            return false;
        }
        if (!list) {
            alert('Please select an allowance or deduction.');
            $('#backdrop').hide();
            return false;
        }
        if (email && !validateEmail(email)) {
            alert('Please enter a valid email address or leave it blank.');
            $('#backdrop').hide();
            return false;
        }
        return true;
    }

    $('#submit').click(function(event) {
        event.preventDefault();
        if (!validateInputs()) return;

        $('#backdrop').css('display', 'flex');
        var $button = $(this);
        $button.prop('disabled', true);
        var selectedOption = $('#pay_list').find(':selected');
        var dataType = selectedOption.data('type');
        var list = selectedOption.val();

        $.ajax({
            type: 'POST',
            url: 'libs/get_report_deductionlist.php',
            data: {
                payperiod: $('#pay_period').val(),
                type: dataType,
                allow_id: list
            },
            success: function(response) {
                if (response === 'Select Deduction/allowance') {
                    alert('Please select an allowance or deduction.');
                    $('#backdrop').hide();
                } else {
                    $('#table').html(response);
                    $('#backdrop').hide();
                }
                $button.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error, xhr.responseText);
                alert('An error occurred while loading the report. Please try again.');
                $('#backdrop').hide();
                $button.prop('disabled', false);
            }
        });
    });

    $('#download-excel-button').click(function() {
        if (!validateInputs()) return;
        var selectedOption = $('#pay_list').find(':selected');
        var dataType = selectedOption.data('type');
        var list = selectedOption.val();
        var period = $('#pay_period').val();
        var email = $('#email').val();
        window.location.href = 'libs/generate_excel_deductionlist.php?payperiod=' + period +
            '&allow_id=' + list + '&type=' + dataType + (email ? '&email=' + encodeURIComponent(email) :
                '');
    });

    $('#export-pdf-button').click(function() {
        if (!validateInputs()) return;
        var selectedOption = $('#pay_list').find(':selected');
        var dataType = selectedOption.data('type');
        var list = selectedOption.val();
        var period = $('#pay_period').val();
        var email = $('#email').val();
        window.location.href = 'libs/generate_pdf_deductionlist.php?payperiod=' + period +
            '&allow_id=' + list + '&type=' + dataType + (email ? '&email=' + encodeURIComponent(email) :
                '');
    });
});
</script>