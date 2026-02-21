<?php
// App class is already initialized in report_analysis.php container
?>

$selectPeriods = $App->selectDrop("SELECT concat(payperiods.description,'-',payperiods.periodYear) as period, 
                                        payperiods.periodId FROM payperiods ORDER BY periodId DESC ");
?>
<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8 px-4">
        <div class="flex justify-between mx-4">
            <h1 class="text-2xl font-bold mb-6">Subvention Analysis</h1>
            <div>
                <button id="export-pdf-button" class="ml-2 mb-2 px-4 py-2 bg-orange-500 text-white rounded-md shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 hidden">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
                <button id="download-excel-button" class="ml-2 mb-2 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 hidden">
                    <i class="fas fa-download"></i> Download Excel
                </button>
            </div>
        </div>

        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow mb-8">
            <h1 class="text-xl font-bold mb-6 text-center">Select Month for Analysis Report</h1>
            <form id="analysisForm" method="POST">
                <div class="mb-4">
                    <label for="period" class="block text-sm font-medium text-gray-700">Month:</label>
                    <select id="period" name="period" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Month</option>
                        <?php
                        foreach ($selectPeriods as $selectPeriod) {
                            echo '<option value="' . $selectPeriod['periodId'] . '">' . $selectPeriod['period'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <button type="submit" id="generateButton" class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>

        <div id="resultContainer" class="hidden font-xs">
            <div class="bg-white border border-gray-200 p-8 shadow-sm print-area">
                <div class="text-center font-bold text-lg mb-4 underline uppercase" id="reportTitle">ANALYSIS FOR THE MONTH OF ...</div>
                
                <table class="min-w-full bg-white border border-gray-400 table-auto" id="analysisTable">
                    <tbody class="text-sm">
                        <!-- Top Summary -->
                        <tr>
                            <td class="py-1 px-2 border border-gray-400 font-bold w-1/2">GROSS</td>
                            <td class="py-1 px-2 border border-gray-400 font-bold text-right" id="val-gross">0.00</td>
                            <td class="py-1 px-2 border border-gray-400"></td>
                        </tr>
                        <tr>
                            <td class="py-1 px-2 border border-gray-400 font-bold">TAX</td>
                            <td class="py-1 px-2 border border-gray-400 font-bold text-right" id="val-tax">0.00</td>
                            <td class="py-1 px-2 border border-gray-400"></td>
                        </tr>
                        <tr class="bg-gray-100">
                            <td class="py-1 px-2 border border-gray-400 font-bold">GROSS AFTER TAX</td>
                            <td class="py-1 px-2 border border-gray-400 font-bold text-right border-b-4 border-double" id="val-net-gross">0.00</td>
                            <td class="py-1 px-2 border border-gray-400"></td>
                        </tr>
                        
                        <!-- Breakdown Title -->
                        <tr>
                            <td colspan="3" class="py-3"></td>
                        </tr>
                        <tr class="bg-gray-200">
                            <td class="py-1 px-2 border border-gray-400 font-bold uppercase" colspan="2">BREAKDOWN ANALYSIS OF GROSS SALARY AFTER TAX</td>
                            <td class="py-1 px-2 border border-gray-400 font-bold text-right" id="val-net-pay-top">0.00</td>
                        </tr>
                        <tr>
                            <td class="py-1 px-2 border border-gray-400 font-bold uppercase">NET PAY</td>
                            <td class="py-1 px-2 border border-gray-400 font-bold text-right bg-gray-50" id="val-net-pay">0.00</td>
                            <td class="py-1 px-2 border border-gray-400"></td>
                        </tr>

                        <!-- Deductions List inserted here dynamically -->
                        <tbody id="deductionsList">
                        </tbody>

                        <!-- Totals -->
                        <tr class="bg-gray-100">
                            <td class="py-2 px-2 border border-gray-400 font-bold uppercase">TOTAL DEDUCTION</td>
                            <td class="py-2 px-2 border border-gray-400"></td>
                            <td class="py-2 px-2 border border-gray-400 font-bold text-right" id="val-total-deduction">0.00</td>
                        </tr>
                        
                        <tr>
                            <td colspan="3" class="py-2"></td>
                        </tr>

                        <!-- Final Payouts -->
                        <tr class="bg-gray-200">
                            <td class="py-2 px-2 border border-gray-400 font-bold uppercase">ACTUAL AMOUNT THAT WILL BE PAID</td>
                            <td class="py-2 px-2 border border-gray-400 font-bold uppercase" colspan="2">NET PAY - TOTAL DEDUCTION</td>
                        </tr>

                        <!-- Retained Deductions inserted here dynamically -->
                        <tbody id="retainedList">
                        </tbody>

                        <tr class="bg-gray-100">
                            <td class="py-2 px-2 border border-gray-400 font-bold uppercase">DEDUCTION RETAINED IN THE SUBVENTION ACCOUNT</td>
                            <td class="py-2 px-2 border border-gray-400 font-bold text-right text-lg" id="val-retained-total">0.00</td>
                            <td class="py-2 px-2 border border-gray-400 font-bold uppercase text-right text-sm leading-tight text-gray-700">ACTUAL AMOUNT +<br>DEDUCTION<br>RETAINED</td>
                        </tr>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<div class="backdrop" id="backdrop" style="display: none;">
    <div class="spinner"></div>
</div>

<script>
$(document).ready(function() {
    function validateInputs() {
        var period = $('#period').val();
        if (!period) {
            alert('Please select a Month.');
            return false;
        }
        return true;
    }

    $('#analysisForm').submit(function(event) {
        event.preventDefault();
        if (!validateInputs()) return;

        $('#backdrop').show();
        var $button = $('#generateButton');
        $button.prop('disabled', true);

        var period = $('#period').val();

        $.ajax({
            type: 'POST',
            url: 'libs/get_report_analysis.php',
            dataType: 'json',
            data: { period: period },
            success: function(response) {
                if (response.status === 'success') {
                    // Update Title
                    $('#reportTitle').text('ANALYSIS FOR THE MONTH OF ' + response.data.period_description);

                    // Update Top Summary
                    $('#val-gross').text(response.data.gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#val-tax').text(response.data.tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#val-net-gross').text(response.data.gross_after_tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    
                    $('#val-net-pay-top').text(response.data.gross_after_tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#val-net-pay').text(response.data.gross_after_tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    // Iterate Deductions
                    $('#deductionsList').empty();
                    response.data.deductions.forEach(function(item) {
                        $('#deductionsList').append(`
                            <tr>
                                <td class="py-1 px-2 border border-gray-400 uppercase">${item.name}</td>
                                <td class="py-1 px-2 border border-gray-400 text-right">${item.amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                <td class="py-1 px-2 border border-gray-400"></td>
                            </tr>
                        `);
                    });

                    // Total Deduction
                    $('#val-total-deduction').text(response.data.total_deductions.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    // Retained
                    $('#retainedList').empty();
                    response.data.retained_deductions.forEach(function(item) {
                        $('#retainedList').append(`
                            <tr>
                                <td class="py-1 px-2 border border-gray-400 uppercase">${item.name}</td>
                                <td class="py-1 px-2 border border-gray-400 text-right">${item.amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                <td class="py-1 px-2 border border-gray-400"></td>
                            </tr>
                        `);
                    });

                    // Retained Total
                    $('#val-retained-total').text(response.data.total_retained.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    // Show container and buttons
                    $('#resultContainer').removeClass('hidden');
                    $('#export-pdf-button').removeClass('hidden');
                    // $('#download-excel-button').removeClass('hidden'); // Excel not requested yet

                } else {
                    alert('Error: ' + response.message);
                }
                
                $('#backdrop').hide();
                $button.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error, xhr.responseText);
                alert('An error occurred while generating the report.');
                $('#backdrop').hide();
                $button.prop('disabled', false);
            }
        });
    });

    $('#export-pdf-button').click(function() {
        if (!validateInputs()) return;
        var period = $('#period').val();
        
        var $button = $(this);
        $button.prop('disabled', true).html('<i class="fas fa-file-pdf"></i> Generating PDF...');
        
        window.location.href = 'libs/generate_pdf_analysis.php?period=' + period;
        
        setTimeout(function() {
            $button.prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Download PDF');
        }, 3000);
    });
});
</script>
