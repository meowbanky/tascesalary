<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['payperiod'])) {
        $period = $_POST['payperiod'];
    }

    if (isset($_POST['pfa'])) {
        $pfa = $_POST['pfa'];
    }

    $getPensions = $App->getPfa($period, $pfa);

    // Start output
    ob_start();
    ?>
    <div class="flex justify-center">
        <div class="overflow-x-auto pr-4 mx-auto" id="payslipModal">
            <table id="table-search" class="min-w-full bg-white">
                <thead>
                <tr class="w-full bg-gray-200">
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">S/N</th>
                    <?php if ($pfa != -1) { ?>
                        <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Staff No</th>
                        <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
                        <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">PFA PIN</th>
                    <?php } ?>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">PFA</th>
                    <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">AMOUNT</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $grossPension = 0;
                $sn = 1;
                if ($getPensions) {
                    $periodDescs = $App->getPeriodDescription($period);
                    $periodDesc = $periodDescs['period'];
                    foreach ($getPensions as $getPension) {
                        ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo $sn; ?></td>
                            <?php if ($pfa != -1) { ?>
                                <td class="border px-4 py-2"><?php echo $getPension['OGNO']; ?></td>
                                <td class="border px-4 py-2"><?php echo $getPension['NAME']; ?></td>
                                <td class="border px-4 py-2"><?php echo $getPension['PFAACCTNO']; ?></td>
                            <?php } ?>
                            <td class="border px-4 py-2"><?php echo $getPension['PFANAME']; ?></td>
                            <td class="border px-4 py-2"><?php echo number_format($getPension['deduc'], 2); ?></td>
                        </tr>
                        <?php
                        $sn++;
                        $grossPension += $getPension['deduc'];
                    }
                }
                ?>
                <tr>
                    <td class="border px-4 py-2"><?php echo ($getPensions ? 'Total' : ''); ?></td>
                    <?php if ($pfa != -1) { ?>
                        <td class="border px-4 py-2"></td>
                        <td class="border px-4 py-2"></td>
                        <td class="border px-4 py-2"></td>
                    <?php } ?>
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