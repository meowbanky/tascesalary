<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$deductions = $App->selectAll("SELECT ed_id, ed, edDesc FROM tbl_earning_deduction WHERE type = 2 ORDER BY ed ASC", []);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Deduction Payees</h1>
        <button id="add-payee-button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150 ease-in-out">
            <i class="fas fa-plus mr-2"></i> Add New Payee
        </button>
    </div>

    <div class="overflow-x-auto">
        <table id="payeeTable" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deduction</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payee Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Split</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="payeeList" class="bg-white divide-y divide-gray-200">
                <!-- Data will be loaded here via AJAX -->
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Loading payees...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="payeeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800" id="modalTitle">Add Deduction Payee</h3>
            <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="payeeForm" class="p-6 space-y-4">
            <input type="hidden" name="id" id="payeeId">
            
            <div>
                <label for="ed_id" class="block text-sm font-medium text-gray-700 mb-1">Deduction</label>
                <select name="ed_id" id="ed_id" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Deduction --</option>
                    <?php foreach ($deductions as $d): ?>
                        <option value="<?php echo $d['ed_id']; ?>"><?php echo htmlspecialchars($d['ed'] . ' - ' . $d['edDesc']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="payee_name" class="block text-sm font-medium text-gray-700 mb-1">Payee Name</label>
                <input type="text" name="payee_name" id="payee_name" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="account_no" class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                    <input type="text" name="account_no" id="account_no" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="fixed_amount" class="block text-sm font-medium text-gray-700 mb-1">Fixed Amount (N)</label>
                    <input type="number" step="0.01" name="fixed_amount" id="fixed_amount" value="0.00" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="percentage" class="block text-sm font-medium text-gray-700 mb-1">Percentage (%)</label>
                    <input type="number" step="0.01" name="percentage" id="percentage" value="0.00" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" id="cancelBtn" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        loadPayees();

        function loadPayees() {
            $.ajax({
                url: 'libs/get_deduction_payees.php',
                type: 'GET',
                success: function(response) {
                    $('#payeeList').html(response);
                },
                error: function() {
                    $('#payeeList').html('<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error loading data.</td></tr>');
                }
            });
        }

        $('#add-payee-button').click(function() {
            $('#modalTitle').text('Add Deduction Payee');
            $('#payeeForm')[0].reset();
            $('#payeeId').val('');
            $('#payeeModal').removeClass('hidden');
        });

        $('#closeModal, #cancelBtn').click(function() {
            $('#payeeModal').addClass('hidden');
        });

        $(document).on('click', '.edit-payee', function() {
            const data = $(this).data();
            $('#modalTitle').text('Edit Deduction Payee');
            $('#payeeId').val(data.id);
            $('#ed_id').val(data.edId);
            $('#payee_name').val(data.payeeName);
            $('#bank_name').val(data.bankName);
            $('#account_no').val(data.accountNo);
            $('#fixed_amount').val(data.fixedAmount);
            $('#percentage').val(data.percentage);
            $('#payeeModal').removeClass('hidden');
        });

        $('#payeeForm').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: 'libs/save_deduction_payee.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayAlert(response.message, 'center', 'success');
                        $('#payeeModal').addClass('hidden');
                        loadPayees();
                    } else {
                        displayAlert(response.message, 'center', 'error');
                    }
                },
                error: function() {
                    displayAlert('An error occurred while saving.', 'center', 'error');
                }
            });
        });

        $(document).on('click', '.delete-payee', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete payee: ${name}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'libs/delete_deduction_payee.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                displayAlert(response.message, 'center', 'success');
                                loadPayees();
                            } else {
                                displayAlert(response.message, 'center', 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>
