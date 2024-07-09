<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['payperiod'])) {
        $period = $_POST['payperiod'];
    }

    if(isset($_POST['list'])){
        $list =    $_POST['list'];
    }

    if(isset($_POST['allow_id'])){
        $allow_id =    $_POST['allow_id'];
    }
    if(isset($_POST['type'])){
        $type =    $_POST['type'];
    }

$allow_deduc = $type == 1 ? 'Allowance' : 'Deduction';

$Deductions = $App->getReportDeductionList($period, $type,$allow_id);
}





?>

<div class="overflow-x-auto">
    <table id="table-search" class="min-w-full bg-white">
        <thead>
        <tr class="w-full bg-gray-200">
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">S/N</th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700"><?php echo $allow_deduc ?></th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">Staff No</th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php $gross=0; $sn= 1; $count=0; if($Deductions){ foreach($Deductions as $Deduction){ ?>
            <tr>
                <td class="border px-4 py-2"><?php echo $sn; ?></td>
                <td class="border px-4 py-2"><?php echo $Deduction['edDesc']; ?></td>
                <td class="border px-4 py-2"><?php echo $Deduction['staff_id']; ?></td>
                <td class="border px-4 py-2"><?=($Deduction['NAME']) ?></td>
                <td class="border px-4 py-2"><?=number_format($Deduction['value']) ?></td>
            </tr>
        <?php $gross +=$Deduction['value'];$sn++;
        }
        } ?>
        <tr>
            <td class="border px-4 py-2 font-bold" colspan="4">Total</td>
            <td class="border px-4 py-2 font-bold"><?=number_format($gross); ?></td>
        </tr>

        </tbody>
    </table>
</div>
<script type="text/javascript">
    var table = document.getElementById('table-search');
    Sorrtty(table)
</script>
