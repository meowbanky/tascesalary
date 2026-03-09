<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
include_once '../libs/get_earning_deductions.php';

?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Earnings & Deductions</h1>
            <div>
                <button id="reload-button" class="ml-2 mb-2 px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-sync-alt"></i> Reload
                </button>
                <button id="add-item-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-plus"></i> Add New
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="table-items" class="min-w-full bg-white border border-gray-200">
                <thead>
                <tr class="w-full bg-gray-800 text-white">
                    <th class="py-2 px-4 border border-gray-300">ID</th>
                    <th class="py-2 px-4 border border-gray-300">Description</th>
                    <th class="py-2 px-4 border border-gray-300">Type</th>
                    <th class="py-2 px-4 border border-gray-300">Is Retained?</th>
                    <th class="py-2 px-4 border border-gray-300">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($items as $item) {
                    $typeText = ($item['type'] == 1) ? '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Earning</span>' : '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Deduction</span>';
                    $retainedText = ($item['is_retained'] == 1) ? '<span class="text-green-600 font-bold"><i class="fas fa-check-circle"></i> Yes</span>' : '<span class="text-gray-400 italic">No</span>';
                    echo "
                        <tr class='hover:bg-gray-50'>
                            <td class='py-2 px-4 border border-gray-300 text-center'>{$item['ed_id']}</td>
                            <td class='py-2 px-4 border border-gray-300'>{$item['ed']}</td>
                            <td class='py-2 px-4 border border-gray-300 text-center'>{$typeText}</td>
                            <td class='py-2 px-4 border border-gray-300 text-center'>{$retainedText}</td>
                            <td class='py-2 px-4 border border-gray-300 text-center'>
                                <button class='edit-button text-blue-500 hover:text-blue-700' 
                                    data-id='{$item['ed_id']}' 
                                    data-name='{$item['ed']}' 
                                    data-type='{$item['type']}' 
                                    data-retained='{$item['is_retained']}'>
                                    <i class='fas fa-edit'></i> Edit
                                </button>
                            </td>
                        </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="itemModal" class="fixed inset-0 items-center justify-center bg-gray-500 bg-opacity-50 hidden z-50">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 id="modalTitle" class="text-xl font-bold">Add Earning/Deduction</h2>
            <button class="closeModalButton text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="saveItemForm" method="POST">
            <input type="hidden" name="ed_id" id="ed_id">
            <div class="mb-4">
                <label for="ed" class="block text-sm font-medium text-gray-700">Description</label>
                <input type="text" name="ed" id="ed" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" id="type" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="1">Earning</option>
                    <option value="2">Deduction</option>
                </select>
            </div>
            <div class="mb-4 flex items-center">
                <input type="checkbox" name="is_retained" id="is_retained" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_retained" class="ml-2 block text-sm text-gray-900">Is Retained? (For Subvention Analysis Report)</label>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" class="closeModalButton px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#reload-button').click(function() {
            $('#loadContent').load('view/view_earning_deduction.php');
        });

        $('#add-item-button').click(function() {
            $('#modalTitle').text('Add Earning/Deduction');
            $('#saveItemForm')[0].reset();
            $('#ed_id').val('');
            $('#itemModal').removeClass('hidden').addClass('flex');
        });

        $('.edit-button').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var type = $(this).data('type');
            var retained = $(this).data('retained');

            $('#modalTitle').text('Edit Earning/Deduction');
            $('#ed_id').val(id);
            $('#ed').val(name);
            $('#type').val(type);
            $('#is_retained').prop('checked', retained == 1);

            $('#itemModal').removeClass('hidden').addClass('flex');
        });

        $('.closeModalButton').click(function() {
            $('#itemModal').addClass('hidden').removeClass('flex');
        });

        $('#saveItemForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: 'libs/save_earning_deduction.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.status == 'success') {
                        $('#itemModal').addClass('hidden').removeClass('flex');
                        displayAlert(response.message, 'center', 'success');
                        $('#loadContent').load('view/view_earning_deduction.php');
                    } else {
                        displayAlert(response.message, 'center', 'error');
                    }
                },
                error: function() {
                    displayAlert('An error occurred while saving.', 'center', 'error');
                }
            });
        });
    });
</script>
