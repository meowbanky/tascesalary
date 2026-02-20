<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$selectPeriods = $App->selectDrop("SELECT concat(payperiods.description,'-',payperiods.periodYear) as period, 
                                        payperiods.periodId FROM payperiods ORDER BY periodId DESC ");

$pfas = $App->selectDrop("SELECT PFACODE, PFANAME FROM tbl_pfa");
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
            <h1 class="text-2xl font-bold mb-6">Pension Report</h1>
            <div>
                <button id="export-pdf-button" class="ml-2 mb-2 px-4 py-2 bg-orange-500 text-white rounded-md shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
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
                    </div>
                    <div class="flex flex-col items-center">
                        <h1 class="text-2xl font-bold text-center"><?php echo $_SESSION['businessname']; ?></h1>
                        <h2 class="text-lg font-semibold mt-1">Pension Report</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="pfa" class="block text-sm font-medium text-gray-700">PFA:</label>
                    <select id="pfa" name="pfa" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select PFA</option>
                        <option value="-1">PFA Analysis</option>
                        <option value="-2">All PFAs</option>
                        <?php
                        foreach ($pfas as $pfa) {
                            echo "<option value='" . $pfa['PFACODE'] . "'>" . $pfa['PFANAME'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="pay_period" class="block text-sm font-medium text-gray-700">Pay Period:</label>
                    <select id="pay_period" name="pay_period" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Period</option>
                        <?php
                        foreach ($selectPeriods as $selectPeriod) {
                            echo '<option ' . ($_SESSION["currentactiveperiod"] == $selectPeriod['periodId'] ? "selected" : "") . ' value="' . $selectPeriod['periodId'] . '">' . $selectPeriod['period'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email (Optional):</label>
                    <input type="email" id="email" name="email" class="w-full mt-1 border border-gray-300 rounded-md p-2" placeholder="Enter email to send report (leave blank to download)">
                </div>

                <div class="flex justify-start mb-4">
                    <button id="submit" type="button" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">Submit</button>
                </div>

                <div id="table" class="overflow-x-auto"></div>
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
            var pay_period = $('#pay_period').val();
            var pfa = $('#pfa').val();
            var email = $('#email').val();
            if (!pay_period) {
                alert('Please select a Pay Period.');
                $('#backdrop').hide();
                return false;
            }
            if (!/^\d+$/.test(pay_period)) {
                alert('Invalid pay period selected.');
                $('#backdrop').hide();
                return false;
            }
            if (!pfa) {
                alert('Please select a PFA.');
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

            $('#backdrop').show();
            var $button = $(this);
            $button.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: 'libs/get_report_pension.php',
                data: {
                    payperiod: $('#pay_period').val(),
                    pfa: $('#pfa').val()
                },
                success: function(response) {
                    $('#table').html(response);
                    // Only initialize DataTables for single PFA selection (not grouped view)
                    if ($('#table-search').length) {
                        $('#table-search').DataTable({
                            searching: false,
                            pageLength: 100,
                            lengthChange: false,
                            ordering: true,
                            dom: '<"flex items-center justify-between my-2"lf>t<"flex items-center justify-between"ip>',
                        });
                    }
                    $('#backdrop').hide();
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
            var pay_period = $('#pay_period').val();
            var pfa = $('#pfa').val();
            var email = $('#email').val();
            window.location.href = 'libs/generate_excel_pension.php?payperiod=' + pay_period + '&pfa=' + pfa + (email ? '&email=' + encodeURIComponent(email) : '');
        });

        $('#export-pdf-button').click(function() {
            if (!validateInputs()) return;
            var pay_period = $('#pay_period').val();
            var pfa = $('#pfa').val();
            var email = $('#email').val();
            window.location.href = 'libs/generate_pdf_pension.php?payperiod=' + pay_period + '&pfa=' + pfa + (email ? '&email=' + encodeURIComponent(email) : '');
        });
    });
</script>
