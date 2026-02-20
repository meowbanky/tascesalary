<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $periodRaw = $_POST['payperiod'] ?? null;
    $period = App::normalizePeriodId($periodRaw);
    $pfa = $_POST['pfa'] ?? null;

    if ($period === null || $pfa === null || $pfa === '') {
        http_response_code(400);
        echo 'Invalid request';
        return;
    }

    $getPensions = $App->getPfa($period, $pfa);

    // Start output
    ob_start();
    ?>
<?php
    $periodDescription = '';
    if (!empty($period)) {
        $periodRow = $App->getPeriodDescription($period);
        if ($periodRow && isset($periodRow['period'])) {
            $periodDescription = $periodRow['period'];
        }
    }

    $pfaName = 'All PFAs';
    if ($pfa == -1) {
        $pfaName = 'PFA Analysis';
    } elseif ($pfa != -2) {
        $pfaRow = $App->getPfaDetails($pfa);
        if ($pfaRow && isset($pfaRow['PFANAME'])) {
            $pfaName = $pfaRow['PFANAME'];
        }
    }
    ?>
<div class="flex justify-between items-center mb-4">
    <div>
        <h2 class="text-xl font-semibold text-gray-800">Pension Report</h2>
        <?php if ($periodDescription): ?>
        <p class="text-sm text-gray-600">Period: <?php echo htmlspecialchars($periodDescription); ?></p>
        <?php endif; ?>
        <p class="text-sm text-gray-600">PFA: <?php echo htmlspecialchars($pfaName); ?></p>
    </div>
    <button id="print-button"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md print:hidden">Print</button>
</div>
<div class="flex justify-center">
    <div class="overflow-x-auto pr-4 mx-auto" id="payslipModal">
        <table id="<?php echo ($pfa == -1) ? 'table-grouped' : 'table-search'; ?>" class="min-w-full bg-white">
            <thead>
                <tr class="w-full bg-gray-200">
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">S/N</th>
                    <?php if ($pfa != -1): ?>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Staff No</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">PFA PIN</th>
                    <?php endif; ?>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">PFA</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grossPension = 0;
                $sn = 1;
                
                if ($getPensions) {
                    if ($pfa == -1) {
                        // Group PFAs into categories
                        $regularPFAs = [];
                        $suspendedPFAs = [];
                        $othersPFAs = [];
                        
                        foreach ($getPensions as $getPension) {
                            $pfaCode = $getPension['PFACODE'] ?? null;
                            if ($pfaCode == 26) {
                                $suspendedPFAs[] = $getPension;
                            } elseif ($pfaCode == 21) {
                                $othersPFAs[] = $getPension;
                            } else {
                                $regularPFAs[] = $getPension;
                            }
                        }
                        
                        // Display Regular PFAs group
                        if (!empty($regularPFAs)) {
                            ?>
                            <tr class="bg-blue-100">
                                <td colspan="3" class="border px-4 py-2 font-bold text-lg">REGULAR PFAs</td>
                            </tr>
                            <?php
                            $regularTotal = 0;
                            foreach ($regularPFAs as $getPension) {
                                ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo $sn; ?></td>
                                    <td class="border px-4 py-2"><?php echo $getPension['PFANAME']; ?></td>
                                    <td class="border px-4 py-2"><?php echo number_format($getPension['deduc'], 2); ?></td>
                                </tr>
                                <?php
                                $sn++;
                                $regularTotal += $getPension['deduc'];
                                $grossPension += $getPension['deduc'];
                            }
                            ?>
                            <tr class="bg-gray-100">
                                <td class="border px-4 py-2 font-bold">Subtotal</td>
                                <td class="border px-4 py-2"></td>
                                <td class="border px-4 py-2 font-bold"><?php echo number_format($regularTotal, 2); ?></td>
                            </tr>
                            <?php
                        }
                        
                        // Display Suspended group
                        if (!empty($suspendedPFAs)) {
                            ?>
                            <tr class="bg-yellow-100">
                                <td colspan="3" class="border px-4 py-2 font-bold text-lg">SUSPENDED</td>
                            </tr>
                            <?php
                            $suspendedTotal = 0;
                            foreach ($suspendedPFAs as $getPension) {
                                ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo $sn; ?></td>
                                    <td class="border px-4 py-2"><?php echo $getPension['PFANAME']; ?></td>
                                    <td class="border px-4 py-2"><?php echo number_format($getPension['deduc'], 2); ?></td>
                                </tr>
                                <?php
                                $sn++;
                                $suspendedTotal += $getPension['deduc'];
                                $grossPension += $getPension['deduc'];
                            }
                            ?>
                            <tr class="bg-gray-100">
                                <td class="border px-4 py-2 font-bold">Subtotal</td>
                                <td class="border px-4 py-2"></td>
                                <td class="border px-4 py-2 font-bold"><?php echo number_format($suspendedTotal, 2); ?></td>
                            </tr>
                            <?php
                        }
                        
                        // Display Others group
                        if (!empty($othersPFAs)) {
                            ?>
                            <tr class="bg-orange-100">
                                <td colspan="3" class="border px-4 py-2 font-bold text-lg">OTHERS</td>
                            </tr>
                            <?php
                            $othersTotal = 0;
                            foreach ($othersPFAs as $getPension) {
                                ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo $sn; ?></td>
                                    <td class="border px-4 py-2"><?php echo $getPension['PFANAME']; ?></td>
                                    <td class="border px-4 py-2"><?php echo number_format($getPension['deduc'], 2); ?></td>
                                </tr>
                                <?php
                                $sn++;
                                $othersTotal += $getPension['deduc'];
                                $grossPension += $getPension['deduc'];
                            }
                            ?>
                            <tr class="bg-gray-100">
                                <td class="border px-4 py-2 font-bold">Subtotal</td>
                                <td class="border px-4 py-2"></td>
                                <td class="border px-4 py-2 font-bold"><?php echo number_format($othersTotal, 2); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        // Single PFA selected - display individual records
                        foreach ($getPensions as $getPension) {
                            ?>
                            <tr>
                                <td class="border px-4 py-2"><?php echo $sn; ?></td>
                                <td class="border px-4 py-2"><?php echo $getPension['OGNO']; ?></td>
                                <td class="border px-4 py-2"><?php echo $getPension['NAME']; ?></td>
                                <td class="border px-4 py-2"><?php echo $getPension['PFAACCTNO']; ?></td>
                                <td class="border px-4 py-2"><?php echo $getPension['PFANAME']; ?></td>
                                <td class="border px-4 py-2"><?php echo number_format($getPension['deduc'], 2); ?></td>
                            </tr>
                            <?php
                            $sn++;
                            $grossPension += $getPension['deduc'];
                        }
                    }
                }
                ?>
                <tr>
                    <td class="border px-4 py-2"><?php echo ($getPensions ? 'Total' : ''); ?></td>
                    <?php if ($pfa != -1): ?>
                    <td class="border px-4 py-2"></td>
                    <td class="border px-4 py-2"></td>
                    <td class="border px-4 py-2"></td>
                    <?php endif; ?>
                    <td class="border px-4 py-2"></td>
                    <td class="border px-4 py-2"><?php echo $getPensions ? number_format($grossPension, 2) : ''; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php
    $output = ob_get_clean();
    echo $output;
}
?>