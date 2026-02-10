<?php
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();

$selectPeriods = $App->selectDrop("SELECT CONCAT(payperiods.description,'-',payperiods.periodYear) AS period, payperiods.periodId FROM payperiods ORDER BY payperiods.periodId DESC");
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="container mx-auto py-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold">Staff Pension History</h1>
            <div class="flex flex-wrap gap-2">
                <button id="download-excel-button"
                    class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-excel"></i> Download Excel
                </button>
                <button id="download-pdf-button"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="staff_search" class="block text-sm font-medium text-gray-700">Staff</label>
                    <input type="text" id="staff_search" class="w-full mt-1 border border-gray-300 rounded-md p-2"
                        placeholder="Type staff ID, name or OGNO">
                    <input type="hidden" id="staff_id">
                    <p id="staff-selected-info" class="text-xs text-gray-500 mt-1 hidden"></p>
                </div>
                <div>
                    <label for="pfa_name_display" class="block text-sm font-medium text-gray-700">PFA</label>
                    <input type="text" id="pfa_name_display"
                        class="w-full mt-1 border border-gray-300 rounded-md p-2 bg-gray-100 text-gray-600" readonly
                        placeholder="Select a staff to see PFA information">
                </div>
                <div>
                    <label for="period_from" class="block text-sm font-medium text-gray-700">Period From</label>
                    <select id="period_from" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Period</option>
                        <?php if ($selectPeriods): ?>
                        <?php foreach ($selectPeriods as $period): ?>
                        <option value="<?php echo htmlspecialchars($period['period']); ?>"
                            data-id="<?php echo htmlspecialchars($period['periodId']); ?>">
                            <?php echo htmlspecialchars($period['period']); ?>
                        </option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label for="period_to" class="block text-sm font-medium text-gray-700">Period To</label>
                    <select id="period_to" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                        <option value="">Select Period</option>
                        <?php if ($selectPeriods): ?>
                        <?php foreach ($selectPeriods as $period): ?>
                        <option value="<?php echo htmlspecialchars($period['period']); ?>"
                            data-id="<?php echo htmlspecialchars($period['periodId']); ?>">
                            <?php echo htmlspecialchars($period['period']); ?>
                        </option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-start mb-4">
                <button id="submit" type="button"
                    class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none">Submit</button>
            </div>

            <div id="table"></div>
        </div>
    </div>
</div>

<div class="backdrop" id="backdrop">
    <div class="spinner"></div>
</div>

<script>
$(document).ready(function() {
    function getPeriodId(selectElement) {
        const selectedOption = $(selectElement).find('option:selected');
        return selectedOption.data('id');
    }

    function validateInputs() {
        const staffId = $('#staff_id').val();
        const periodFrom = getPeriodId('#period_from');
        const periodTo = getPeriodId('#period_to');

        if (!staffId) {
            alert('Please search and select a staff member.');
            return false;
        }
        if (!periodFrom || !periodTo) {
            alert('Please select both start and end periods.');
            return false;
        }
        if (!/^\d+$/.test(String(periodFrom)) || !/^\d+$/.test(String(periodTo))) {
            alert('Invalid pay period selected.');
            return false;
        }
        if (parseInt(periodFrom, 10) > parseInt(periodTo, 10)) {
            alert('Period From cannot be greater than Period To.');
            return false;
        }
        return true;
    }

    $('#staff_search').autocomplete({
        source: 'libs/searchstaff.php',
        minLength: 3,
        delay: 200,
        select: function(event, ui) {
            event.preventDefault();
            $('#staff_search').val(ui.item.label);
            $('#staff_id').val(ui.item.value);
            $('#staff-selected-info').removeClass('hidden').text('Selected Staff: ' + ui.item
                .label);

            // Reset PFA display until report loads
            $('#pfa_name_display').val('');
        }
    });

    $('#staff_search').on('input', function() {
        if (!$(this).val()) {
            $('#staff_id').val('');
            $('#staff-selected-info').addClass('hidden').text('');
            $('#pfa_name_display').val('');
        }
    });

    function collectRequestPayload() {
        const periodFrom = getPeriodId('#period_from');
        const periodTo = getPeriodId('#period_to');
        return {
            staff_id: $('#staff_id').val(),
            period_from: periodFrom ? parseInt(periodFrom, 10) : '',
            period_to: periodTo ? parseInt(periodTo, 10) : ''
        };
    }

    $('#submit').on('click', function(event) {
        event.preventDefault();
        if (!validateInputs()) {
            return;
        }

        $('#backdrop').css('display', 'flex');
        const $button = $(this);
        $button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: 'libs/get_report_staff_pension.php',
            data: collectRequestPayload(),
            success: function(response) {
                $('#table').html(response);
                $('#backdrop').hide();
                $button.prop('disabled', false);

                const pfaName = $('#table').find('[data-pfa-name]').data('pfa-name') || '';
                $('#pfa_name_display').val(pfaName);

                const historyTable = $('#table').find('#staff-pension-table');
                if (historyTable.length) {
                    historyTable.DataTable({
                        searching: false,
                        paging: false,
                        ordering: false,
                        info: false,
                        dom: 't'
                    });
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

    function buildDownloadUrl(baseUrl) {
        if (!validateInputs()) {
            return null;
        }
        const payload = collectRequestPayload();
        const params = new URLSearchParams(payload);
        return baseUrl + '?' + params.toString();
    }

    $('#download-excel-button').on('click', function() {
        const url = buildDownloadUrl('libs/generate_excel_staff_pension.php');
        if (url) {
            window.location.href = url;
        }
    });

    $('#download-pdf-button').on('click', function() {
        const url = buildDownloadUrl('libs/generate_pdf_staff_pension.php');
        if (url) {
            window.location.href = url;
        }
    });
});
</script>