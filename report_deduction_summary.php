<?php
require_once 'libs/App.php';
$App = new App();
$App->checkAuthentication();

require_once 'libs/middleware.php';
checkPermission('report_deduction_summary.php');

include 'partials/main.php';
?>
<head>
    <?php
    $title = "Deduction Summary Report";
    include 'partials/title-meta.php';
    ?>
    <?php include 'partials/head-css.php'; ?>
</head>

<body>
<div class="flex wrapper">
    <?php include 'partials/menu.php'; ?>

    <div class="page-content">
        <?php include 'partials/topbar.php'; ?>

        <main class="flex-grow p-6">
            <?php
            $subtitle = "Reports";
            $pagetitle = "Deduction Summary Report";
            include 'partials/page-title.php';
            ?>

            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <form id="reportForm" class="flex flex-wrap items-end gap-4">
                    <div class="w-full md:w-1/3">
                        <label for="payperiod" class="block text-sm font-medium text-gray-700 mb-1">Select Pay Period</label>
                        <select name="payperiod" id="payperiod" required class="block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">-- Select Period --</option>
                            <?php
                            $periods = $App->selectAll("SELECT * FROM payperiods ORDER BY periodId DESC", []);
                            foreach ($periods as $p) {
                                echo "<option value='{$p['periodId']}'>{$p['description']} {$p['periodYear']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-search mr-2"></i> Generate Report
                    </button>
                    <div id="exportButtons" class="hidden flex gap-2">
                        <button type="button" id="exportExcel" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <i class="fas fa-file-excel mr-2"></i> Excel
                        </button>
                        <button type="button" id="exportPdf" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            <i class="fas fa-file-pdf mr-2"></i> PDF
                        </button>
                    </div>
                </form>
            </div>

            <div id="reportResult" class="bg-white p-6 rounded-lg shadow-md overflow-hidden min-h-[400px]">
                <div class="flex justify-center items-center h-full text-gray-400">
                    <p>Select a period and click "Generate Report" to view results.</p>
                </div>
            </div>
        </main>

        <?php include 'partials/footer.php'; ?>
    </div>
</div>

<?php include 'partials/customizer.php'; ?>
<?php include 'partials/footer-scripts.php'; ?>

<script>
    $(document).ready(function() {
        $('#reportForm').submit(function(e) {
            e.preventDefault();
            const payperiod = $('#payperiod').val();
            
            $('#reportResult').html('<div class="flex justify-center items-center h-full"><i class="fas fa-spinner fa-spin fa-3x text-blue-500"></i></div>');
            
            $.ajax({
                url: 'libs/get_report_deduction_summary.php',
                type: 'POST',
                data: { payperiod: payperiod },
                success: function(response) {
                    $('#reportResult').html(response);
                    $('#exportButtons').removeClass('hidden');
                },
                error: function() {
                    $('#reportResult').html('<div class="text-red-500 text-center">An error occurred while generating the report.</div>');
                }
            });
        });

        $('#exportExcel').click(function() {
            const payperiod = $('#payperiod').val();
            window.location.href = `libs/generate_excel_deduction_summary.php?payperiod=${payperiod}`;
        });

        $('#exportPdf').click(function() {
            const payperiod = $('#payperiod').val();
            window.location.href = `libs/generate_pdf_deduction_summary.php?payperiod=${payperiod}`;
        });
    });
</script>
</body>
</html>
