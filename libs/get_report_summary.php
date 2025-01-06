<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['delete'])) {
        $deletions = $_POST['delete'];
        $deleteSuccessfully = '';
        // Process each deletion
        foreach ($deletions as $delete) {
            list($temp_id, $staff_id) = explode('/', $delete);
            $array_deletions = [
                ':temp_id' => $temp_id
            ];
            $deleteSuccessfully = $App->selectOne("DELETE FROM allow_deduc WHERE temp_id = :temp_id",$array_deletions);

        }
        echo "Selected allowances have been deleted.";
    }
}

if(isset($_POST['payperiod'])) {
    $period = $_POST['payperiod'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $allowanceSummarys = $App->getReportSummary($period, 'allow');
    $deductionSummarys = $App->getReportSummary($period, 'deduc');
}else{
    $allowanceSummarys = $App->getReportSummary(-1, 'allow');
    $deductionSummarys = $App->getReportSummary(-1, 'deduc');
}






?>

<div class="overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead>
        <tr class="w-full bg-gray-200">
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Code Description</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Amount</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="border px-4 py-2 font-bold" colspan="2">Allowances</td>

        </tr>
        <?php $gross=0; if($allowanceSummarys){ foreach($allowanceSummarys as $allowanceSummary){ ?>
            <tr>
                <td class="border px-4 py-2"><?php echo $allowanceSummary['edDesc']; ?></td>
                <td class="border px-4 py-2"><?=number_format($allowanceSummary['value']) ?></td>
            </tr>
        <?php $gross +=$allowanceSummary['value'];
        }
        } ?>
        <tr>
            <td class="border px-4 py-2 font-bold">Gross</td>
            <td class="border px-4 py-2 font-bold"><?=number_format($gross); ?></td>
        </tr>
        <tr>
            <td class="border px-4 py-2 font-bold" colspan="2">Deductions</td>

        </tr>
        <?php $deduction=0; if($deductionSummarys){ foreach($deductionSummarys as $deductionSummary){ ?>
            <tr>
                <td class="border px-4 py-2"><?php echo $deductionSummary['edDesc']; ?></td>
                <td class="border px-4 py-2"><?=number_format($deductionSummary['value']) ?></td>
            </tr>
            <?php $deduction +=$deductionSummary['value'];
        }
        } ?>
        <tr>
            <td class="border px-4 py-2 font-bold">Deduction</td>
            <td class="border px-4 py-2 font-bold"><?=number_format($deduction); ?></td>
        </tr>
        <tr>
            <td class="border px-4 py-2 font-bold">Net</td>
            <td class="border px-4 py-2 font-bold"><?=number_format($gross-$deduction); ?></td>
        </tr>
        </tbody>
    </table>
</div>
