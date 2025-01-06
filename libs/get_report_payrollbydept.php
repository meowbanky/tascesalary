<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['payperiod'])) {
        $period = $_POST['payperiod'];
    }

    if(isset($_POST['bank'])) {
        $bank = $_POST['bank'];
    }
    if(isset($_POST['dept'])){
        $dept = $_POST['dept'];
    }else{
        $dept = null;
    }
$GrossPays = $App->getBankSummaryGroupBy($period,'master_staff.DEPTCD',$dept);
}


?>
<div class="flex justify-center">
<div class="overflow-x-auto pr-4 mx-auto">
    <table id="table-search" class="min-w-full bg-white">
        <thead>
        <tr class="w-full bg-gray-200">
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">S/N</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700"><?php if($dept == null) {echo "Department Name";}else{echo "Name";} ?></th>
            <?php if($dept != null) { ?>
                <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Salary Structure</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Grade</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Step</th>
            <?php } ?>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">No of Staff</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Total Allowance</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Total Deduction</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Net</th>
        </tr>
        </thead>
        <tbody>
        <?php $gross=0; $deduct=0;$sn= 1; if($GrossPays){
            $periodDescs = $App->getPeriodDescription($period);
            $periodDesc = $periodDescs['period'];
            $totalStaff=0; $net=0;
            foreach($GrossPays as $GrossPay){

                ?>
            <tr>
                <td class="border px-4 py-2"><?php echo intval($sn) ?></td>
                <td class="border px-4 py-2"><?php if($dept == null) {echo $GrossPay['dept'];}else{echo $GrossPay['NAME'];} ?></td>
                <?php if($dept != null) { ?>
                    <td class="border px-4 py-2"><?php echo $GrossPay['SalaryType']; ?></td>
                    <td class="border px-4 py-2"><?php echo intval($GrossPay['grade']); ?></td>
                    <td class="border px-4 py-2"><?php echo intval($GrossPay['step']); ?></td>
                <?php } ?>
                <td class="border px-4 py-2"><?php echo $GrossPay['staff_count']; ?></td>
                <td class="border px-4 py-2"><?php echo number_format($GrossPay['allow']); ?></td>
                <td class="border px-4 py-2"><?php echo number_format($GrossPay['deduc']); ?></td>
                <td class="border px-4 py-2"><?php echo number_format($GrossPay['allow']-$GrossPay['deduc']); ?></td>
           </tr>
        <?php $sn++;
        $gross+=$GrossPay['allow'];
        $deduct+=$GrossPay['deduc'];
        $totalStaff += $GrossPay['staff_count'];
            }
        } ?>
        <tr>
            <td class="border px-4 py-2 font-medium" colspan="<?php if($dept != null) { echo 5;}else{echo 2;} ?>"><?php echo 'Total' ?></td>
            <td class="border px-4 py-2 font-medium"><?php echo  $totalStaff; ?></td>
            <td class="border px-4 py-2 font-medium"><?php echo number_format($gross); ?></td>
            <td class="border px-4 py-2 font-medium"><?php echo number_format($deduct); ?></td>
            <td class="border px-4 py-2 font-medium"><?php echo number_format($gross-$deduct); ?></td>
        </tr>

        </tbody>
    </table>
</div>
</div>

<script type="text/javascript">
    // $('#table-search').DataTable({
    //     searching: false,
    //     pageLength: 100,
    //     lengthChange: false,
    //     ordering: true,
    //     dom: '<"flex  items-center justify-between my-2"lf>t<"flex items-center justify-between"ip>',
    // });
</script>

