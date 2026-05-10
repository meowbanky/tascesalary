<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$query = "SELECT dp.*, ed.ed, ed.edDesc 
          FROM tbl_deduction_payee dp 
          JOIN tbl_earning_deduction ed ON dp.ed_id = ed.ed_id 
          ORDER BY ed.ed ASC, dp.payee_name ASC";
$payees = $App->selectAll($query, []);

if ($payees) {
    foreach ($payees as $p) {
        $bankInfo = !empty($p['bank_name']) ? "{$p['bank_name']} ({$p['account_no']})" : "<i>No bank info</i>";
        $splitInfo = "";
        if ($p['fixed_amount'] > 0) $splitInfo .= "N" . number_format($p['fixed_amount'], 2) . " (Fixed)";
        if ($p['percentage'] > 0) {
            if ($splitInfo) $splitInfo .= " + ";
            $splitInfo .= $p['percentage'] . "%";
        }
        if (!$splitInfo) $splitInfo = "0.00";

        echo "
        <tr class='hover:bg-gray-50 transition duration-150'>
            <td class='px-6 py-4'>
                <div class='text-sm font-semibold text-gray-900'>{$p['ed']}</div>
                <div class='text-xs text-gray-500'>{$p['edDesc']}</div>
            </td>
            <td class='px-6 py-4 text-sm text-gray-700 font-medium'>
                " . htmlspecialchars($p['payee_name']) . "
            </td>
            <td class='px-6 py-4 text-sm text-gray-600'>
                $bankInfo
            </td>
            <td class='px-6 py-4 text-right text-sm font-bold text-gray-900'>
                $splitInfo
            </td>
            <td class='px-6 py-4 text-center space-x-2'>
                <button class='edit-payee p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition' 
                        data-id='{$p['id']}' 
                        data-ed-id='{$p['ed_id']}' 
                        data-payee-name=\"" . htmlspecialchars($p['payee_name']) . "\" 
                        data-bank-name=\"" . htmlspecialchars($p['bank_name']) . "\" 
                        data-account-no=\"" . htmlspecialchars($p['account_no']) . "\" 
                        data-fixed-amount='{$p['fixed_amount']}' 
                        data-percentage='{$p['percentage']}'>
                    <i class='fas fa-edit'></i>
                </button>
                <button class='delete-payee p-1.5 text-red-600 hover:bg-red-50 rounded-md transition' 
                        data-id='{$p['id']}' 
                        data-name=\"" . htmlspecialchars($p['payee_name']) . "\">
                    <i class='fas fa-trash'></i>
                </button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500 italic'>No payees configured. Click 'Add New Payee' to start.</td></tr>";
}
