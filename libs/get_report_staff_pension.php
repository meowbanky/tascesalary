<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo 'Invalid request method.';
    exit;
}

$staffIdRaw = $_POST['staff_id'] ?? null;
$periodFromRaw = $_POST['period_from'] ?? null;
$periodToRaw = $_POST['period_to'] ?? null;

$periodFrom = App::normalizePeriodId($periodFromRaw);
$periodTo = App::normalizePeriodId($periodToRaw);

if ($staffIdRaw === null || $periodFrom === null || $periodTo === null) {
    http_response_code(400);
    echo 'Please provide staff and period range.';
    exit;
}

if (!ctype_digit((string)$staffIdRaw)) {
    http_response_code(400);
    echo 'Invalid parameters.';
    exit;
}

$staffId = (int)$staffIdRaw;

if ($periodFrom > $periodTo) {
    http_response_code(400);
    echo 'Invalid period range.';
    exit;
}

$profile = $App->getStaffProfile($staffId);

if (!$profile) {
    http_response_code(404);
    echo 'Staff record not found.';
    exit;
}

$history = $App->getStaffPensionHistory($staffId, $periodFrom, $periodTo);
$periodFromDesc = $App->getPeriodDescription($periodFrom);
$periodToDesc = $App->getPeriodDescription($periodTo);

$periodFromLabel = $periodFromDesc['period'] ?? $periodFrom;
$periodToLabel = $periodToDesc['period'] ?? $periodTo;

$pfaName = $profile['PFANAME'] ?? '';

ob_start();
?>
<div data-pfa-name="<?php echo htmlspecialchars($pfaName); ?>">
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Staff Information</h3>
                <p class="text-sm text-gray-600">Name: <span
                        class="font-medium text-gray-900"><?php echo htmlspecialchars($profile['NAME'] ?? ''); ?></span>
                </p>
                <p class="text-sm text-gray-600">Staff No: <span
                        class="font-medium text-gray-900"><?php echo htmlspecialchars($profile['staff_id'] ?? ''); ?></span>
                </p>
                <p class="text-sm text-gray-600">OGNO: <span
                        class="font-medium text-gray-900"><?php echo htmlspecialchars($profile['OGNO'] ?? ''); ?></span>
                </p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Pension Details</h3>
                <p class="text-sm text-gray-600">PFA: <span
                        class="font-medium text-gray-900"><?php echo htmlspecialchars($profile['PFANAME'] ?? ''); ?></span>
                </p>
                <p class="text-sm text-gray-600">PFA Code: <span
                        class="font-medium text-gray-900"><?php echo htmlspecialchars($profile['PFACODE'] ?? ''); ?></span>
                </p>
                <p class="text-sm text-gray-600">PFA PIN: <span
                        class="font-medium text-gray-900"><?php echo htmlspecialchars(App::maskAccountNumber($profile['PFAACCTNO'] ?? '')); ?></span>
                </p>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <p>Period Range: <span
                    class="font-medium text-gray-900"><?php echo htmlspecialchars($periodFromLabel . ' to ' . $periodToLabel); ?></span>
            </p>
        </div>
    </div>

    <?php if (!$history): ?>
    <div class="text-center text-gray-500 py-6">
        No pension contributions found for the selected period range.
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table id="staff-pension-table" class="min-w-full bg-white rounded-lg border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">#</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Period</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Amount (₦)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                $sn = 1;
                foreach ($history as $row):
                    $amount = (float)($row['amount'] ?? 0);
                    $total += $amount;
                    ?>
                <tr class="border-b">
                    <td class="px-4 py-2 text-sm text-gray-700"><?php echo $sn++; ?></td>
                    <td class="px-4 py-2 text-sm text-gray-700">
                        <?php echo htmlspecialchars($row['period_name'] ?? $row['period']); ?></td>
                    <td class="px-4 py-2 text-sm text-gray-700"><?php echo number_format($amount, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 font-semibold">
                    <td class="px-4 py-2 text-sm text-gray-700" colspan="2">Total</td>
                    <td class="px-4 py-2 text-sm text-gray-700"><?php echo number_format($total, 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php
echo ob_get_clean();