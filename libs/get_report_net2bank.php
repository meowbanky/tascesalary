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
$GrossPays = $App->getBankSummary($period,$bank);
}


?>
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #payslipModal, #payslipModal * {
            visibility: visible;
        }
        #payslipModal {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }

</style>
<div class="flex justify-center">
<div class="overflow-x-auto pr-4 mx-auto payslipModal">
    <table id="table-search" class="min-w-full bg-white">
        <thead>
        <tr class="w-full bg-gray-200">
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">S/N</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Amount</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Payment Date</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Ben Code</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">ACCOUNT NO  3</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">BANK CODE</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">DEBIT ACCOUNT</th>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">BANK 3 (1ST BANK SAVINGS)</th>
        </tr>
        </thead>
        <tbody>
        <?php $gross=0; $deduct=0;$sn= 1; if($GrossPays){
            $periodDescs = $App->getPeriodDescription($period);
            $periodDesc = $periodDescs['period'];
            foreach($GrossPays as $GrossPay){
                $padded = str_pad($GrossPay['staff_id'],3,"0",0);
                $sm_padd = str_pad($sn,3,"0",0);?>
            <tr>
                <td class="border px-4 py-2"><?php echo 'TASCE_'.$periodDesc.' SAL_'.$sm_padd; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['NAME']; ?></td>
                <td class="border px-4 py-2"><?php echo number_format($GrossPay['allow']-$GrossPay['deduc']); ?></td>
                <td class="border px-4 py-2"><?php echo date('d/m/Y') ?></td>
                <td class="border px-4 py-2"><?php echo 'TASCE '.$padded; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['acctno']; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['bankcode']; ?></td>
                <td class="border px-4 py-2"><?php echo '0051719443'; ?></td>
                <td class="border px-4 py-2"><?php echo $GrossPay['bankname'];?></td>
           </tr>
        <?php $sn++;
            }
        } ?>


        </tbody>
    </table>
</div>
</div>
<script type="text/javascript">
    var table = document.getElementById('table-search');
    Sorrtty(table)
</script>
