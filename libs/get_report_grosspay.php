<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['payperiod'])) {
        $period = $_POST['payperiod'];
    }


$GrossPays = $App->getBankSummary($period);
}


?>
<div class="flex justify-center">
<div class="overflow-x-auto pr-4 mx-auto">
    <table id="table-search" class="min-w-full bg-white">
        <thead>
        <tr class="w-full bg-gray-200">
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">S/N</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Staff No</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Statu</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Salary Structure</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Department</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Grade/Step</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Account No</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Bank</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Gross</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Deduction</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Net</th>
        </tr>
        </thead>
        <tbody>
        <?php $gross=0; $deduct=0;$sn= 1; if($GrossPays){ foreach($GrossPays as $GrossPay){ ?>
            <tr>
                <td class="border px-4 py-2"><?php echo $sn; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['OGNO']; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['NAME']; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['STATUSCD']; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['SalaryType']; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['dept']; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['grade']."/".$GrossPay['step']; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['acctno']; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['bankname']; ?></td>
                <td class="border px-4 py-2"><?php echo number_format($GrossPay['allow']); ?></td>
                <td class="border px-4 py-2"><?php echo number_format($GrossPay['deduc']); ?></td>
                <td class="border px-4 py-2"><?=number_format($GrossPay['allow']-$GrossPay['deduc']) ?></td>
            </tr>
        <?php $gross +=$GrossPay['allow']; $deduct +=$GrossPay['deduc'];$sn++;
        }
        } ?>
        <tr>
            <td class="border px-4 py-2 font-bold" colspan="8">Total</td>
            <td class="border px-4 py-2 font-bold"><?=number_format($gross); ?></td>
            <td class="border px-4 py-2 font-bold"><?=number_format($deduct); ?></td>
            <td class="border px-4 py-2 font-bold"><?=number_format($gross-$deduct); ?></td>

        </tr>

        </tbody>
    </table>
</div>
</div>
<script type="text/javascript">
  
</script>
