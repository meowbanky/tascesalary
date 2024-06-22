<?php
require '../libs/App.php';

$App = new App;

$selectDrops = $App->selectDrop();

?>
<style>
    /* Spinner and backdrop styles */
    .backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .spinner {
        border: 16px solid #f3f3f3;
        border-top: 16px solid #3498db;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        animation: spin 2s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
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
                <?php
                foreach ($selectDrops as $selectDrop) {
                    echo '<option value="' . $selectDrop['ed_id'] . '">' . $selectDrop['ed'] . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="counter" class="block text-sm font-medium text-gray-700">Counter</label>
            <input type="number" value = '0' id="counter" name="counter" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div class="text-center mt-4">
            <button type="button" id="send-files" class="btn bg-violet-500 border-violet-500 text-white">Send Files</button>
        </div>
    </div>
</div>
<div class="backdrop" id="backdrop">
    <div class="spinner"></div>
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
                formData.append("counter", counter);
                formData.append("allow_id", allow_id);
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

