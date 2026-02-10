<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$selectPeriods = $App->selectDrop("SELECT concat(payperiods.description,'-',payperiods.periodYear) as period, 
	                                    payperiods.periodId FROM payperiods ORDER BY periodId DESC ");


?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
        <h1 class="text-2xl font-bold mb-6">Export for Gross</h1>
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
                        <h1 class="text-2xl font-bold"<?php echo  $_SESSION['businessname']; ?></h1>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold"> </h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="pay-period" class="block text-sm font-medium text-gray-700">Pay Period :</label>
                    <select id="pay_period" name="pay_period" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Item</option>
                        <?php
                        foreach ($selectPeriods as $selectPeriod) {
                            echo '<option ';if($_SESSION["currentactiveperiod"] == $selectPeriod['periodId']) {echo "selected"; };echo ' value="' . $selectPeriod['periodId'] . '">' . $selectPeriod['period'] . '</option>';
                        }
                        ?>
                    </select>
                    <div class="flex justify-start m-2">
                        <button id="submit" type="button" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">Submit</button>
                    </div>
                </div>

                <div id="table"></div>

                <div class="flex justify-end mt-4">

                </div>
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
            var modalMaxHeight = screenHeight - 60; // Subtract 60px from screen height
            $('.modal-content').css('max-height', 70 + 'vh');
        }
            $('#download-excel-button').click(function() {
                var period = $('#pay_period').val();
                window.location.href = 'libs/getexportgross.php?payperiod='+period;
            });

            $('#submit').click(function(event) {
                document.getElementById("backdrop").style.display = "flex";
                event.preventDefault();
                var period = $('#pay_period').val();
                window.location.href = 'libs/getexportgross.php?payperiod='+period;
                $('#backdrop').hide();
            });

        });

</script>

