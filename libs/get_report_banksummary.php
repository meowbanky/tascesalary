<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if(isset($_POST['payperiod'])) {
    $period = $_POST['payperiod'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $banksummary = $App->getBankSummaryGroupBy($period);
}





?>

<div class="overflow-x-auto">
    <table id="table-search" class="min-w-full bg-white">
        <thead>
        <tr class="w-full bg-gray-200">
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">Bank Name</th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">No. of Staff</th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">Netpay</th>
        </tr>
        </thead>
        <tbody>
        <?php $gross=0; $count=0; if($banksummary){ foreach($banksummary as $summary){ ?>
            <tr>
                <td class="border px-4 py-2"><?php echo $summary['BNAME']; ?></td>
                <td class="border px-4 py-2"><?=($summary['staff_count']) ?></td>
                <td class="border px-4 py-2"><?=number_format($summary['net']) ?></td>
            </tr>
        <?php $gross +=$summary['net'];$count +=$summary['staff_count'];
        }
        } ?>
        <tr>
            <td class="border px-4 py-2 font-bold">Total</td>
            <td class="border px-4 py-2 font-bold"><?=number_format($count); ?></td>
            <td class="border px-4 py-2 font-bold"><?=number_format($gross); ?></td>
        </tr>

        </tbody>
    </table>
</div>
<script type="text/javascript">
    $('#table-search').DataTable({
        searching: false,
        pageLength: 100,
        lengthChange: false,
        ordering: true,
        dom: '<"flex  items-center justify-between my-2"lf>t<"flex items-center justify-between"ip>',

    });
</script>
