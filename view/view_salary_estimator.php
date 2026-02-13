<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6 text-blue-600"><i class="mgc_chart_line_line mr-2"></i>Promotion Cost Estimator</h1>
        <p class="mb-6 text-gray-600">Estimate the financial impact of staff promotions by calculating projected allowance changes.</p>

        <!-- Configuration Panel -->
        <div class="bg-white border border-gray-200 p-6 rounded-lg shadow mb-8">
            <h2 class="text-lg font-semibold mb-4 border-b border-gray-200 pb-2 text-gray-700">Configuration</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="searchStaff" class="block text-sm font-medium mb-1 text-gray-700">Search Staff</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="mgc_search_line text-gray-400"></i>
                        </span>
                        <input type="text" id="searchStaff" class="w-full bg-white border border-gray-300 rounded-md py-2 pl-10 pr-10 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Type name or staff ID...">
                        <button type="button" id="clearSearch" class="absolute inset-y-0 right-0 inline-flex items-center pr-3 text-gray-400 hover:text-red-500" style="display: none;">
                            <i class="mgc_close_line text-lg"></i>
                        </button>
                        <input type="hidden" id="selectedStaffId">
                    </div>
                     <div id="selectedStaffInfo" class="mt-2 text-sm text-green-600 font-medium hidden">
                        <i class="mgc_check_circle_line mr-1"></i> Current: Grade <span id="currentGradeDisplay"></span>, Step <span id="currentStepDisplay"></span>
                    </div>
                </div>

                <!-- Add Grade -->
                <div>
                    <label for="addGrade" class="block text-sm font-medium mb-1 text-gray-700">Add Grade Levels (+/-)</label>
                    <input type="number" id="addGrade" value="0" class="w-full bg-white border border-gray-300 rounded-md py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Add Step -->
                <div>
                    <label for="addStep" class="block text-sm font-medium mb-1 text-gray-700">Add Steps (+/-)</label>
                    <input type="number" id="addStep" value="0" class="w-full bg-white border border-gray-300 rounded-md py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button id="btnAddToList" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md shadow transition duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="mgc_add_line mr-2"></i> Add to List
                </button>
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Estimation Results</h2>
                <div class="space-x-2">
                     <button id="btnExport" class="text-green-600 hover:text-green-800 font-medium hidden">
                        <i class="mgc_file_excel_line mr-1"></i> Export to Excel
                    </button>
                    <button id="btnClear" class="text-red-600 hover:text-red-800 font-medium hidden">
                        <i class="mgc_delete_bin_line mr-1"></i> Clear List
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left" id="estimationTable">
                    <thead class="bg-gray-800 text-white uppercase text-xs">
                        <tr>
                            <th class="py-3 px-4">Staff</th>
                            <th class="py-3 px-4">Current Level</th>
                            <th class="py-3 px-4">New Level</th>
                            <th class="py-3 px-4 text-right">Current Monthly</th>
                            <th class="py-3 px-4 text-right">New Monthly</th>
                            <th class="py-3 px-4 text-right">Difference</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="estimationBody" class="text-sm divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500 italic">No staff added yet. Search and add staff above.</td>
                        </tr>
                    </tbody>
                    <tfoot id="estimationFoot" class="bg-gray-100 font-bold hidden">
                        <tr>
                            <td colspan="3" class="py-3 px-4 text-right">TOTALS:</td>
                            <td class="py-3 px-4 text-right" id="ftTotalCurrent">0.00</td>
                            <td class="py-3 px-4 text-right" id="ftTotalNew">0.00</td>
                            <td class="py-3 px-4 text-right" id="ftTotalDiff">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
    let estimationList = [];

    // Initialize Autocomplete
    $("#searchStaff").autocomplete({
        source: 'libs/searchstaff.php',
        type: 'POST',
        delay: 300,
        minLength: 2,
        select: function(event, ui) {
            event.preventDefault();
            $("#searchStaff").val(ui.item.label);
            $("#selectedStaffId").val(ui.item.value);
            
            // Show Current Level Info
            $("#currentGradeDisplay").text(ui.item.GRADE);
            $("#currentStepDisplay").text(ui.item.STEP);
            $("#selectedStaffInfo").removeClass('hidden');

            // Show Clear Button
            $("#clearSearch").show();

            $('#btnAddToList').prop('disabled', false);
        }
    });

    // Clear Search Handler
    $('#clearSearch').click(function() {
        $("#searchStaff").val('');
        $("#selectedStaffId").val('');
        $("#selectedStaffInfo").addClass('hidden');
        $(this).hide();
        $('#btnAddToList').prop('disabled', true);
    });

    $('#btnAddToList').click(function() {
        const staffId = $('#selectedStaffId').val();
        const addGrade = $('#addGrade').val();
        const addStep = $('#addStep').val();

        if(!staffId) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Input',
                text: 'Please select a staff member first.'
            });
            return;
        }

        // Disable button while processing
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="mgc_loading_line animate-spin mr-2"></i> Processing...');

        $.ajax({
            url: 'libs/estimate_upgrade.php',
            type: 'POST',
            dataType: 'json',
            data: {
                staff_id: staffId,
                add_grade: addGrade,
                add_step: addStep
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="mgc_add_line mr-2"></i> Add to List');
                if(response.status === 'success') {
                    // Add to list
                    estimationList.push(response);
                    renderTable();
                    
                    // Reset Search
                    $("#searchStaff").val('');
                    $("#selectedStaffId").val('');
                    $('#btnAddToList').prop('disabled', true);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="mgc_add_line mr-2"></i> Add to List');
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'An error occurred while calculating.'
                });
            }
        });
    });

    // Delegated delete event
    $('#estimationBody').on('click', '.delete-row', function() {
        const index = $(this).data('index');
        estimationList.splice(index, 1);
        renderTable();
    });

    $('#btnClear').click(function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You act will clear the current list!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear it!'
        }).then((result) => {
            if (result.isConfirmed) {
                estimationList = [];
                renderTable();
                Swal.fire(
                    'Cleared!',
                    'The list has been cleared.',
                    'success'
                );
            }
        });
    });

    $('#btnExport').click(function() {
        if(estimationList.length === 0) return;

        // Prepare data for Excel
        let data = [
            ["Staff Name", "Current Level", "New Level", "Current Monthly", "New Monthly", "Difference"]
        ];

        estimationList.forEach(item => {
            data.push([
                item.staff_name,
                item.current_level,
                item.new_level,
                item.current_monthly,
                item.new_monthly,
                item.difference
            ]);
        });

        // Add totals row
        let totals = calculateTotals();
        data.push(["TOTALS", "", "", totals.current, totals.new, totals.diff]);

        // Create workbook
        var wb = XLSX.utils.book_new();
        var ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Estimates");
        XLSX.writeFile(wb, "Promotion_Estimates.xlsx");
    });

    function renderTable() {
        const tbody = $('#estimationBody');
        tbody.empty();

        if (estimationList.length === 0) {
            tbody.html('<tr><td colspan="7" class="py-8 text-center text-gray-500 italic">No staff added yet. Search and add staff above.</td></tr>');
            $('#estimationFoot').addClass('hidden');
            $('#btnExport, #btnClear').addClass('hidden');
            return;
        }

        $('#estimationFoot').removeClass('hidden');
        $('#btnExport, #btnClear').removeClass('hidden');

        estimationList.forEach((item, index) => {
            const diffClass = item.difference > 0 ? 'text-green-600 font-bold' : (item.difference < 0 ? 'text-red-600 font-bold' : 'text-gray-600');
            
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 font-medium text-gray-900">${item.staff_name}</td>
                    <td class="py-3 px-4 text-gray-600">${item.current_level}</td>
                    <td class="py-3 px-4 text-gray-600">${item.new_level}</td>
                    <td class="py-3 px-4 text-right">${numberWithCommas(item.current_monthly)}</td>
                    <td class="py-3 px-4 text-right">${numberWithCommas(item.new_monthly)}</td>
                    <td class="py-3 px-4 text-right ${diffClass}">${numberWithCommas(item.difference)}</td>
                    <td class="py-3 px-4 text-center">
                        <button class="text-red-500 hover:text-red-700 delete-row" data-index="${index}" title="Remove">
                            <i class="mgc_delete_line text-lg"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Update Totals
        const totals = calculateTotals();
        $('#ftTotalCurrent').text(numberWithCommas(totals.current));
        $('#ftTotalNew').text(numberWithCommas(totals.new));
        
        const totalDiffClass = totals.diff > 0 ? 'text-green-600' : (totals.diff < 0 ? 'text-red-600' : 'text-gray-600');
        $('#ftTotalDiff').html(`<span class="${totalDiffClass}">${numberWithCommas(totals.diff)}</span>`);
    }

    function calculateTotals() {
        let current = 0;
        let newTotal = 0;
        let diff = 0;

        estimationList.forEach(item => {
            current += parseFloat(item.current_monthly);
            newTotal += parseFloat(item.new_monthly);
            diff += parseFloat(item.difference);
        });

        return { current, new: newTotal, diff };
    }

    function numberWithCommas(x) {
        return parseFloat(x).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
});
</script>
