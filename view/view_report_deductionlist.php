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
        <h1 class="text-2xl font-bold mb-6 print:!block"> Allowance/Deduction List</h1>
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
                        <h2 class="text-lg font-semibold">Allowance/Deductio List </h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16 print">
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

                </div>

                <div class="mb-4">
                    <label for="pay_list" class="block text-sm font-medium text-gray-700">List :</label>
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
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="border border-gray-300 rounded-md p-2 w-full">
                </div>
                <div class="mb-4">
                    <div class="flex justify-start m-2 gap-2">
                        <button id="submit" type="button" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">Submit</button>
                        <button id="emailButton" type="button" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">@Send mail</button>

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

            $('#emailButton').on('click', function() {
                if($('#email').val() == '') {
                    const email = prompt("Please enter the employee's email address:");
                }else {
                    email = $('#email').val();
                }

                if (email) {
                    document.getElementById("backdrop").style.display = "flex";
                    var selectedOption = $('#pay_list').find(':selected');
                    var dataType = selectedOption.data('type');
                    var list = selectedOption.val();
                    $.ajax({
                        url: 'libs/mail_excel_deductionlist.php',
                        type: 'GET',
                        data: {
                            payperiod: $('#pay_period').val(),
                            type: dataType,
                            allow_id:list,
                            email:email
                        },
                        success: function(response) {
                            $('#backdrop').hide(); // Hide the backdrop and spinner
                            alert('List emailed successfully.');
                        },
                        error: function(xhr, status, error) {
                            $('#backdrop').hide(); // Hide the backdrop and spinner
                            alert('Error sending email: ' + error);
                        }
                    });
                }
            });

        function setModalMaxHeight() {
            var screenHeight = window.innerHeight;
            var modalMaxHeight = screenHeight - 60; // Subtract 60px from screen height
            $('.modal-content').css('max-height', 70 + 'vh');
        }
            $('#download-excel-button').click(function() {
                var selectedOption = $('#pay_list').find(':selected');
                var dataType = selectedOption.data('type');
                var list = selectedOption.val();
                var period = $('#pay_period').val();

                window.location.href = 'libs/generate_excel_deductionlist.php?payperiod='+period+'&allow_id='+list+'&type='+dataType;
            });
// Print function
            function printContent() {
                var printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>Print Content</title>');
                printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">');
                printWindow.document.write('</head><body>');
                printWindow.document.write($('#table').html());
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.print();
            }

            // Attach print function to the button click
            $('#print-button').click(function() {
                printContent();
            });
            $('#submit').click(function(event) {
                document.getElementById("backdrop").style.display = "flex";
                event.preventDefault();
                var selectedOption = $('#pay_list').find(':selected');
                var dataType = selectedOption.data('type');
                var list = selectedOption.val();


                $.ajax({
                    type: 'POST',
                    url: 'libs/get_report_deductionlist.php',
                    data: {
                        payperiod: $('#pay_period').val(),
                        type: dataType,
                        allow_id:list
                    },
                    success: function(response) {
                        if(response === 'Select Deduction/allowance'){
                            displayAlert(response,'center','error');
                            document.getElementById("backdrop").style.display = "none";
                        }else {
                            $('#table').html(response);
                            document.getElementById("backdrop").style.display = "none";
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                        document.getElementById("backdrop").style.display = "none";
                    }
                });
            });

        });

</script>

