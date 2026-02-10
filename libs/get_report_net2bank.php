<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$GrossPays = [];
$periodDescription = '';
$bankName = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $periodRaw = $_POST['payperiod'] ?? null;
    $period = App::normalizePeriodId($periodRaw);
    $bank = $_POST['bank'] ?? null;

    if ($period === null) {
        http_response_code(400);
        echo 'Invalid pay period selected.';
        return;
    }

    $GrossPays = $App->getBankSummary($period, $bank);

    $periodDescription = '';
    if (!empty($period)) {
        $periodRow = $App->getPeriodDescription($period);
        if ($periodRow && isset($periodRow['period'])) {
            $periodDescription = $periodRow['period'];
        }
    }

    if ($bank == -1) {
        $bankName = 'All Banks';
    } else {
        if (method_exists($App, 'getBankName')) {
            $bankRow = $App->getBankName($bank);
        } else {
            $bankRow = $App->getBanksDetails($bank);
        }
        $bankName = $bankRow['BNAME'] ?? 'Selected Bank';
    }
}


?>
<style>
@media print {
    body * {
        visibility: hidden;
    }

    #payslipModal,
    #payslipModal * {
        visibility: visible;
    }

    #payslipModal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
<div class="flex justify-center">
    <div class="overflow-x-auto pr-4 mx-auto payslipModal">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Net to Bank</h2>
                <?php if (!empty($periodDescription)): ?>
                <p class="text-sm text-gray-600">Period: <?php echo htmlspecialchars($periodDescription); ?></p>
                <?php endif; ?>
                <p class="text-sm text-gray-600">Bank: <?php echo htmlspecialchars($bankName ?? ''); ?></p>
            </div>
            <button id="print-button"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md print:hidden">Print</button>
        </div>
        <?php if (empty($GrossPays)): ?>
        <div class="text-center text-gray-500 py-6">
            No data available for the selected period and bank.
        </div>
        <?php else: ?>
        <table id="table-search" class="min-w-full bg-white">
            <thead>
                <tr class="w-full bg-gray-200">
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">S/N</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">NAME</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">STATUS</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">AMOUNT</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">PAYMENT DATE
                    </th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">BENEFICIARY
                        CODE</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">ACCOUNT</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">BANK CODE</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">DEBIT ACCOUNT
                    </th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">BANK</th>
                </tr>
            </thead>
            <tbody>
                <?php $gross=0; $deduct=0;$sn= 1;
            foreach($GrossPays as $GrossPay){
                $padded = str_pad($GrossPay['staff_id'],3,"0",0);
                $sm_padd = str_pad($sn,3,"0",0);
                ?>
                <tr>
                    <td class="border px-4 py-2"><?php echo 'TASCE_'.($periodDescription ?? '').'_SAL_'.$sm_padd; ?>
                    </td>
                    <td class="border px-4 py-2"><?php echo $GrossPay['NAME']; ?></td>
                    <td class="border px-4 py-2"><?php echo $GrossPay['STATUSCD']; ?></td>
                    <td class="border px-4 py-2"><?php echo number_format($GrossPay['allow']-$GrossPay['deduc']); ?>
                    </td>
                    <td class="border px-4 py-2"><?php echo date('d/m/Y') ?></td>
                    <td class="border px-4 py-2"><?php echo 'TASCE '.$padded; ?></td>
                <td class="border px-4 py-2"><?php echo App::maskAccountNumber($GrossPay['acctno']); ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['bankcode']; ?></td>
                    <td class="border px-4 py-2"><?php echo '1229191715'; ?></td>
                    <td class="border px-4 py-2"><?php echo $GrossPay['bankname'];?></td>
                </tr>
                <?php $sn++;
            }
         ?>


            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<script type="text/javascript">
<?php if (!empty($GrossPays)): ?>
$('#table-search').DataTable({
    searching: false,
    pageLength: 100,
    lengthChange: false,
    ordering: true,
    dom: '<"flex  items-center justify-between my-2"lf>t<"flex items-center justify-between"ip>',

});
<?php endif; ?>

$('#print-button').on('click', function() {
    window.print();
});
</script>