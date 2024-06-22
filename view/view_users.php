

<div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <form method="POST" action="create_user.php">
        <div class="mb-4">
            <input type="hidden" name="staff_id" id="staff_id">
            <input type="hidden" name="email" id="email">
            <label for="employee_id" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" readonly name="employee_name" id="employee_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <label for="roles_id" class="block text-sm font-medium text-gray-700">Roles</label>
            <select id="roles_id" name="roles_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Select Role</option>
                <option value="1">Admin</option>
                <option value="2">Editor</option>
                <option value="3">Viewer</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" readonly id="username" name="username" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="text-center mt-4">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create User</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {

        $("#search").focus();
        $("#search").select();
        $("#search").autocomplete({
            source: 'libs/searchStaff.php',
            type: 'POST',
            delay: 10,
            autoFocus: false,
            minLength: 3,
            select: function (event, ui) {
                event.preventDefault();
                $("#staff_id").val(ui.item.value);
                $('#employee_name').val(ui.item.label);
                $('#email').val(ui.item.EMAIL);
                $("#username").val(ui.item.value);
            }
        });


    })
</script>