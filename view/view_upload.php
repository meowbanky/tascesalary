<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$selectDrops = $App->selectDrop();
?>

<div class="w-full max-w-md p-4">
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Upload Allowances/Deductions</h1>
        <form action="libs/process_upload.php" class="dropzone" id="file-dropzone">
            <div class="dz-message needsclick w-full text-center">
                <div class="mb-3">
                    <i class="mgc_upload_3_line text-4xl text-gray-300 dark:text-gray-200"></i>
                </div>
                <h5 class="text-xl text-gray-600 dark:text-gray-200">Drop files here or click to upload.</h5>
            </div>
        </form>
        <div class="mb-4">
            <label for="allowDedSelect" class="block text-sm font-medium text-gray-700 mt-2">Allow/Ded</label>
            <select id="allow_id" name="allow_id" class="employee-select w-full mt-1 block rounded-md border-gray-300">
                <option value="">Select Item</option>
                <?php foreach ($selectDrops as $selectDrop) : ?>
                    <option value="<?= $selectDrop['ed_id'] ?>"><?= $selectDrop['ed'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="counter" class="block text-sm font-medium text-gray-700">Counter</label>
            <input type="number" value="0" id="counter" name="counter" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="mb-4">
            <label for="add_remove" class="block text-sm font-medium text-gray-700">Add/Remove</label>
            <select id="add_remove" name="add_remove" class="employee-select w-full mt-1 block rounded-md border-gray-300">
                <option value="1">Add Item</option>
                <option value="-1">Remove Item</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="verification" class="block text-sm font-medium text-gray-700">Verification Column</label>
            <select id="verification" name="verification" class="employee-select w-full mt-1 block rounded-md border-gray-300">
                <option value="OGNO">Staff ID</option>
                <option value="staff_id">Serial No</option>
                <option value="NAME">Name</option>
            </select>
        </div>
        <div class="text-center mt-4 gap-2">
            <button type="button" id="send-files" class="btn bg-violet-500 border-violet-500 text-white">Send Files</button>

            <a href="template.xlsx" class="btn bg-blue-600 border-blue-600 text-white ml-4" download>Download Template</a>
        </div>
    </div>
</div>
<div class="backdrop" id="backdrop">
    <div class="spinner">Processing</div>
</div>
<script>
    Dropzone.autoDiscover = false;
    var myDropzone = new Dropzone("#file-dropzone", {
        url: "libs/process_upload.php",
        autoProcessQueue: false,
        acceptedFiles: ".xlsx",
        init: function() {
            var dz = this;

            document.getElementById("send-files").addEventListener("click", function() {
                if (dz.getQueuedFiles().length > 0) {
                    document.getElementById("backdrop").style.display = "flex";
                    dz.processQueue();
                } else {
                    displayAlert('No file uploaded','center','error');
                }
            });

            dz.on("sending", function(file, xhr, formData) {
                var counter = $('#counter').val();
                var allow_id = $('#allow_id').val();
                var add_remove = $('#add_remove').val();
                var verification = $('#verification').val();
                formData.append("counter", counter);
                formData.append("allow_id", allow_id);
                formData.append("add_remove", add_remove);
                formData.append("verification", verification);

            });

            dz.on("complete", function(file) {
                dz.removeFile(file);
            });

            dz.on("success", function(file, response) {
                document.getElementById("backdrop").style.display = "none";
                if (response.status = 'success') {
                    displayAlert(response.message, 'center', 'success');
                } else {
                    displayAlert(response.message, 'center', 'error');
                }
            });

            dz.on("error", function(file, response) {
                document.getElementById("backdrop").style.display = "none";
                var errorMessage = (typeof response === 'object' && response.message) ? response.message : 'An error occurred during upload.';
                displayAlert(errorMessage, 'center', 'error');
            });
        }
    });

</script>
