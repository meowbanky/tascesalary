<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
include_once '../libs/getdept.php';

?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex justify-between">
            <h1 class="text-2xl font-bold mb-6">Dept Lists</h1>
            <div>
                <button id="reload-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-sync-alt"></i>Reload
                </button>
                <button id="download-excel-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-download"></i> Download Excel
                </button>
                <button id="add-employee-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-plus"></i> Add New
                </button>
            </div>
        </div>
        <div class="overflow-x-auto font-xs">
            <table id="table-search" class="min-w-full bg-white border border-gray-200">
                <thead>
                <tr class="w-full bg-gray-800 text-white">
                    <th class="sortable py-2 px-4 border border-gray-300">Dept ID</th>
                    <th class="sortable py-2 px-4 border border-gray-300">Dept</th>
                    <th class="sortable py-2 px-4 border border-gray-300">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (isset($depts['dept_auto'])) {
                    $depts = [$depts];
                }
                foreach ($depts as $dept) {
                    echo "
                        <tr class='bg-gray-50'>
                            <td class='py-2 px-4 border border-gray-300'>{$dept['dept_auto']}</td>
                            <td class='py-2 px-4 border border-gray-300'>{$dept['dept']}</td>
                             <td class='py-2 px-4 border border-gray-300 text-center'>
                                <div class='flex justify-center'>
                                   <button class='edit-button text-blue-500 hover:text-blue-700 mx-1' data-dept_id='{$dept['dept_auto']}'  data-name='{$dept['dept']}' ><i class='fas fa-edit'></i></button>
                                    
                                     </div>
                            </td>
                        </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50 hidden">
    <div class="scrollable-content bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Edit Dept</h2>
            <button id="closeModalButton" class="closeModalButton text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <form id="createBankForm" method="POST">
        <div class="mb-4">
            <input type="hidden" name="dept_id" id="dept_id">
            <label for="dept_name" class="block text-sm font-medium text-gray-700">Dept Name</label>
            <input type="text" name="dept_name" id="dept_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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

        $("#search").focus();
        $("#search").select();

        $('#reload-button').on('click', function(event) {
            event.preventDefault();
            $('#loadContent', window.parent.document).load('view/view_dept.php');
        })

        $('#createBankForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: 'libs/add_dept.php', // PHP script to handle form submission
                dataType: 'json',
                data: formData,
                success: function(response) {
                    if(response.status == 'success') {
                        $('#editModal').addClass('hidden');
                        displayAlert(response.message,'center', 'success');
                        $('#loadContent', window.parent.document).load('view/view_dept.php');
                    }else{
                        displayAlert(response.message,'center', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    console.log(error);
                }
            });
        });

        $('.edit-button').click(function() {
            var dept_id = $(this).data('dept_id');
            var dept_name = $(this).data('name');


            $('#dept_id').val(dept_id);
            $('#dept_name').val(dept_name);

            $('#editModal').removeClass('hidden');
        });

        // Close Modal
        $('.closeModalButton').click(function() {
            $('#editModal').addClass('hidden');
        });

        // Close Modal
        $('#add-employee-button').click(function() {
            $('#editModal').removeClass('hidden');
        });

    })
</script>