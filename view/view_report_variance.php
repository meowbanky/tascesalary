<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();


$selectPeriods = $App->selectDrop("SELECT concat(payperiods.description,'-',payperiods.periodYear) as period, 
	                                    payperiods.periodId FROM payperiods ORDER BY periodId DESC ");

?>
<style>
    @media print {
        body * {
            visibility: visible;
        }
        #result2, * {
            visibility: visible;
        }
        #result2 {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }

</style>
<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8 px-4">
        <div class="flex justify-between mx-4">
        <h1 class="text-2xl font-bold mb-6">Variance</h1>
            <div>

                <div>
                    <button id="export-pdf-button" class="ml-2 mb-2 px-4 py-2 bg-orange-500 text-white rounded-md shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </button>
                    <button id="download-excel-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-download"></i> Download Excel
                    </button>
                </div>

            </div>
        </div>

        <div class="container mx-auto p-4">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center space-x-4">
                        <img src="assets/images/ogun_logo.png" alt="Logo" class="w-16 h-16">
                        <h1 class="text-2xl font-bold"<? echo  $_SESSION['businessname']; ?></h1>
                    </div>

                    <div class="flex items-center space-x-4">
                        <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16">
                    </div>
                </div>
                <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
                    <h1 class="text-2xl font-bold mb-6 text-center">Compare Gross Salary Between Months</h1>
                    <form id="varianceForm" method="POST">
                <div class="mb-4">
                    <label for="month1" class="block text-sm font-medium text-gray-700">Month1 :</label>
                    <select id="month1" name="month1" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Month 1</option>
                        <?php
                        foreach ($selectPeriods as $selectPeriod) {
                            echo '<option value="' . $selectPeriod['periodId'] . '">' . $selectPeriod['period'] . '</option>';
                        }
                        ?>
                    </select>

                </div>
                <div class="mb-4">
                    <label for="month2" class="block text-sm font-medium text-gray-700">Month2 :</label>
                    <select id="month2" name="month2" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Month 2</option>
                        <?php
                        foreach ($selectPeriods as $selectPeriod) {
                            echo '<option value="' . $selectPeriod['periodId'] . '">' . $selectPeriod['period'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <div class="flex justify-start m-2">
                        <button type="submit" id="compareButton" class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">
                            Compare
                        </button>
                    </div>
                </div>
                    </form>
                </div>
                <div id="result2" class="overflow-x-auto font-xs hidden">
                    <h2 class="text-xl font-bold mb-4 text-center">Variance Result</h2>
                    <table id="table-search" class="min-w-full bg-white border border-gray-200">
                        <thead id="resultHead">
                        <tr class="w-full bg-gray-800 text-white">
                            <th class="py-2 px-4 border border-gray-300">Staff ID</th>
                            <th class="py-2 px-4 border border-gray-300">Name</th>
                            <th class="py-2 px-4 border border-gray-300">Month 1 Gross Salary</th>
                            <th class="py-2 px-4 border border-gray-300">Month 2 Gross Salary</th>
                            <th class="py-2 px-4 border border-gray-300">Difference</th>
                            <th class="py-2 px-4 border border-gray-300">Remark</th>
                        </tr>
                        </thead>
                        <tbody id="resultBody">
                        <!-- Results will be inserted here by JavaScript -->
                        </tbody>
                    </table>
                    
                    <div class="flex justify-end mt-4">
                        <button id="export-pdf-button-bottom" class="ml-2 px-4 py-2 bg-orange-500 text-white rounded-md shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
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
        $('#table-search').DataTable({
            searching: false,
            pageLength: 100,
            lengthChange: false,
            ordering: true,
            dom: '<"flex items-center justify-between my-2"lf>t<"flex items-center justify-between"ip>',
        });

        function setModalMaxHeight() {
        var screenHeight = window.innerHeight;
        var modalMaxHeight = screenHeight - 60;
        $('.modal-content').css('max-height', '70vh');
    }

        function validateInputs() {
        var month1 = $('#month1').val();
        var month2 = $('#month2').val();
        if (!month1) {
        alert('Please select Month 1.');
        $('#backdrop').hide();
        return false;
    }
        if (!month2) {
        alert('Please select Month 2.');
        $('#backdrop').hide();
        return false;
    }
        if (month1 === month2) {
        alert('Please select two different months.');
        $('#backdrop').hide();
        return false;
    }
        return true;
    }

    // Function to save all remarks (including auto-generated ones)
    function saveAllRemarks(callback) {
        var month1 = $('#month1').val();
        var month2 = $('#month2').val();
        var remarksSaved = 0;
        var totalRemarks = $('.variance-remark').length;
        
        if (totalRemarks === 0) {
            if (callback) callback();
            return;
        }
        
        $('.variance-remark').each(function() {
            var $textarea = $(this);
            var staffId = $textarea.data('staff-id');
            var remark = $textarea.val();
            
            $.ajax({
                type: 'POST',
                url: 'libs/save_variance_remark.php',
                data: {
                    staff_id: staffId,
                    month1: month1,
                    month2: month2,
                    remark: remark
                },
                dataType: 'json',
                success: function(response) {
                    remarksSaved++;
                    if (remarksSaved === totalRemarks && callback) {
                        callback();
                    }
                },
                error: function() {
                    remarksSaved++;
                    if (remarksSaved === totalRemarks && callback) {
                        callback();
                    }
                }
            });
        });
    }

        $('#varianceForm').submit(function(event) {
        event.preventDefault();
        if (!validateInputs()) return;

        $('#backdrop').show();
        var $button = $('#compareButton');
        $button.prop('disabled', true);

        var formData = $(this).serialize();
        $.ajax({
        type: 'POST',
        url: 'libs/get_variance.php',
        dataType: 'json',
        data: formData,
        success: function(response) {
        if (response.status === 'success') {
        var grandtotal = 0;
        $('#resultBody').empty();
        $('#resultHead').empty();

        var month1_description = response.month1_description;
        var month2_description = response.month2_description;

        var rowHead = `<tr class="w-full bg-gray-800 text-white">
                        <th class="py-2 px-4 border border-gray-300">Staff ID</th>
                        <th class="py-2 px-4 border border-gray-300">Name</th>
                        <th class="py-2 px-4 border border-gray-300">${month1_description}</th>
                        <th class="py-2 px-4 border border-gray-300">${month2_description}</th>
                        <th class="py-2 px-4 border border-gray-300">Difference</th>
                        <th class="py-2 px-4 border border-gray-300">Remark</th>
                    </tr>`;
        $('#resultHead').append(rowHead);

        response.data.forEach(function(item) {
        var row = `<tr class="bg-gray-50">
                            <td class="py-2 px-4 border border-gray-300">${item.staff_id}</td>
                            <td class="py-2 px-4 border border-gray-300">${item.name}</td>
                            <td class="py-2 px-4 border border-gray-300">${Number(item.month1_gross).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="py-2 px-4 border border-gray-300">${Number(item.month2_gross).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="py-2 px-4 border border-gray-300">${Number(item.difference).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="py-2 px-4 border border-gray-300">
                                <textarea 
                                    class="variance-remark w-full p-1 border border-gray-300 rounded" 
                                    data-staff-id="${item.staff_id}" 
                                    rows="2"
                                    placeholder="Enter remark...">${item.remark || ''}</textarea>
                            </td>
                        </tr>`;
        $('#resultBody').append(row);
        grandtotal += Number(item.difference);
    });

        var row2 = `
                    <tr class="bg-gray-50">
                        <td colspan="5" class="py-2 px-4 border border-gray-300 font-bold">Difference Total</td>
                        <td class="py-2 px-4 border border-gray-300 font-bold">${grandtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="py-2 px-4 border border-gray-300"></td>
                    </tr>`;
        $('#resultBody').append(row2);

        $('#result2').removeClass('hidden');
        $('#backdrop').hide();
        $button.prop('disabled', false);
        
        // Attach blur event to save remarks
        $('.variance-remark').on('blur', function() {
            var $textarea = $(this);
            var staffId = $textarea.data('staff-id');
            var remark = $textarea.val();
            var month1 = $('#month1').val();
            var month2 = $('#month2').val();
            
            $.ajax({
                type: 'POST',
                url: 'libs/save_variance_remark.php',
                data: {
                    staff_id: staffId,
                    month1: month1,
                    month2: month2,
                    remark: remark
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $textarea.css('border-color', '#10b981');
                        setTimeout(function() {
                            $textarea.css('border-color', '');
                        }, 1000);
                    } else {
                        alert('Error saving remark: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving remark:', error);
                    alert('Failed to save remark. Please try again.');
                }
            });
        });
    } else {
        alert('Error: ' + response.message);
        $('#backdrop').hide();
        $button.prop('disabled', false);
    }
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
        
        var $button = $(this);
        var originalText = $button.text();
        
        // Disable button and show loading state
        $button.prop('disabled', true).text('Saving remarks...');
        
        var month1 = $('#month1').val();
        var month2 = $('#month2').val();
        
        // Save all remarks before downloading
        saveAllRemarks(function() {
            $button.html('<i class="fas fa-download"></i> Generating Excel...');
            window.location.href = 'libs/generate_excel_variance.php?month1=' + month1 + '&month2=' + month2;
            
            // Re-enable button after a delay
            setTimeout(function() {
                $button.prop('disabled', false).html('<i class="fas fa-download"></i> Download Excel');
            }, 2000);
        });
    });

    $('#export-pdf-button, #export-pdf-button-bottom').click(function() {
        if (!validateInputs()) return;
        
        var $button = $(this);
        var originalText = $button.text();
        
        // Disable button and show loading state
        $button.prop('disabled', true).text('Saving remarks...');
        
        var month1 = $('#month1').val();
        var month2 = $('#month2').val();
        
        // Save all remarks before downloading
        saveAllRemarks(function() {
            $button.html('<i class="fas fa-file-pdf"></i> Generating PDF...');
            window.location.href = 'libs/generate_pdf_variance.php?month1=' + month1 + '&month2=' + month2;
            
            // Re-enable button after a delay
            setTimeout(function() {
                $button.prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Download PDF');
            }, 2000);
        });
    });
    });
</script>
