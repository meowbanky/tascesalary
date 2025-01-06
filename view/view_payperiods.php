<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
include_once '../libs/getpayperiods.php';
$currentYear = date('Y');
$currentMonth = date('n'); // Month without leading zeros

// Months array
$months = array(
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
);

?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
        <h1 class="text-2xl font-bold mb-6">Payperiod List</h1>
            <div>
            <button id="add-employee-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-plus"></i> Add New
            </button>
            </div>
        </div>
        <div class="overflow-x-auto font-xs">
            <table id="table-search" class="min-w-full bg-white border border-gray-200">
                <thead>
                <tr class="w-full bg-gray-800 text-white">
                    <th class="sortable py-2 px-4 border border-gray-300">Payment period</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (isset($payPeriodDatas['description'])) {
                    $payPeriodDatas = [$payPeriodDatas];
                }
                foreach ($payPeriodDatas as $payPeriodData) {
                    echo "
                        <tr class='bg-gray-50'>
                            <td class='py-2 px-4 border border-gray-300'>{$payPeriodData['description']}</td>
                          
                        </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div id="editModal" class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50 hidden">
    <div class="scrollable-content bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Add Payperiod</h2>
            <button id="closeModalButton" class="closeModalButton text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addPayPeriod"  method="POST">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <div class="col-md-7">
                <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="perioddesc">
                    <?php for ($monthNumber = $currentMonth; $monthNumber <= 12; $monthNumber++) {
                        $monthName = $months[$monthNumber - 1];?>

                        <option value="<?php echo $monthName ?>"><?php echo $monthName ?></option>

                    <?php }
                    ?>
                </select>
            </div>


                <label class="block text-sm font-medium text-gray-700">Year</label>

                    <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="periodyear">
                        <option value="<?php echo date('Y') ?>"><?php echo date('Y') ?></option>
                        <option value="<?php echo date('Y') + 1 ?>"><?php echo date('Y') + 1 ?></option>
                    </select>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none">Save Changes</button>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-1">
                <button type="submit" id="saveButton" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Save</button>
                <button type="button" class="closeModalButton mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto closebutton">Cancel</button>
            </div>
        </form>
    </div>
</div>

    <script>

        $(document).ready(function() {

            var table = document.getElementById('table-search');
            Sorrtty(table)

        function setModalMaxHeight() {
            var screenHeight = window.innerHeight;
            var modalMaxHeight = screenHeight - 60; // Subtract 60px from screen height
            $('.modal-content').css('max-height', 70 + 'vh');
        }

            $('#addPayPeriod').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: 'libs/getpayperiods.php', // PHP script to handle form submission
                    data: formData,
                    success: function(response) {
                        if(response === 'period saved successfully') {
                            $('#addModal').addClass('hidden');
                            displayAlert(response,'center', 'success');
                            $('#loadContent', window.parent.document).load('view/view_payperiods.php');
                        }else{
                            displayAlert(response,'center', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        console.log(error);
                    }
                });
            });



            $('#add-employee-button').click(function() {

                $('#editModal').removeClass('hidden');
            })



            // Close Modal
            $('.closeModalButton').click(function() {
                $('#editModal').addClass('hidden');
            });


            // Hide the modal-prorate
            $('.closebutton').click(function() {
                $('.closemodal').addClass('hidden');
            });




        });

</script>

