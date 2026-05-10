<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['payperiod'])) {
    $period = $_POST['payperiod'];
    $summary = $App->getDeductionSummaryWithPayees($period);
    
    $periodDesc = $App->getPeriodDescription($period);
    $periodText = $periodDesc['period'] ?? '';
?>

<div class="mb-6 text-center">
    <h2 class="text-xl font-bold uppercase">SIKIRU ADETONA COLLEGE OF EDUCATION SCIENCE AND TECHNOLOGY, OMU-AJOSE</h2>
    <?php $formattedPeriod = str_replace('-', '. ', strtoupper($periodText)); ?>
    <h3 class="text-lg font-semibold uppercase">DEDUCTIONS FOR THE MONTH OF <?php echo $formattedPeriod; ?></h3>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-300">
        <thead>
            <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left border border-gray-300">S/N</th>
                <th class="py-3 px-6 text-left border border-gray-300">PAYEE</th>
                <th class="py-3 px-6 text-left border border-gray-300">BANK NAME</th>
                <th class="py-3 px-6 text-left border border-gray-300">ACCOUNT NO</th>
                <th class="py-3 px-6 text-right border border-gray-300">AMOUNT</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php 
            $total = 0;
            $sn = 1;
            if ($summary) {
                foreach ($summary as $item) {
                    // Only include entries with bank details as requested
                    if (empty($item['bank_name']) || empty($item['account_no'])) {
                        continue;
                    }
                    $total += $item['amount'];
            ?>
            <tr class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6 text-left border border-gray-300"><?php echo $sn++; ?></td>
                <td class="py-3 px-6 text-left border border-gray-300"><?php echo htmlspecialchars($item['payee_name']); ?></td>
                <td class="py-3 px-6 text-left border border-gray-300"><?php echo htmlspecialchars($item['bank_name']); ?></td>
                <td class="py-3 px-6 text-left border border-gray-300"><?php echo htmlspecialchars($item['account_no']); ?></td>
                <td class="py-3 px-6 text-right border border-gray-300 font-bold"><?php echo number_format($item['amount'], 2); ?></td>
            </tr>
            <?php 
                }
            } else {
            ?>
            <tr>
                <td colspan="5" class="py-3 px-6 text-center border border-gray-300">No data found for the selected period.</td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="bg-gray-100 text-gray-900 font-bold uppercase text-sm">
                <td colspan="4" class="py-3 px-6 text-right border border-gray-300">TOTAL</td>
                <td class="py-3 px-6 text-right border border-gray-300"><?php echo number_format($total, 2); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
}
?>
