<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['payperiod'])) {
        $period = $_POST['payperiod'];
    }

    if(isset($_POST['pfa'])) {
        $pfa = $_POST['pfa'];
    }
$getPensions = $App->getPfa($period,$pfa);
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
            <?php if($pfa != -1){ ?>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
                <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">PFA PIN</th>
            <?php } ?>
            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">PFA</th>

            <th class="sortable sm:text-xs px-4 py-2 text-left text-sm font-medium text-gray-700">AMOUNT</th>
             </tr>
        </thead>
        <tbody>
        <?php $grossPension=0; $sn= 1; if($getPensions){
            $periodDescs = $App->getPeriodDescription($period);
            $periodDesc = $periodDescs['period'];
            foreach($getPensions as $getPension){
                ?>
            <tr>
                <td class="border px-4 py-2"><?php echo $sn ?></td>
                <?php if($pfa != -1){ ?>
                <td class="border px-4 py-2"><?php echo $getPension['NAME']; ?></td>
                    <td class="border px-4 py-2"><?php echo $getPension['PFAACCTNO']; ?></td>
                <?php } ?>
                <td class="border px-4 py-2"><?php echo $getPension['PFANAME']; ?></td>

                <td class="border px-4 py-2"><?php echo number_format($getPension['deduc']); ?></td>
           </tr>
        <?php $sn++;
            $grossPension += $getPension['deduc'];
            }
        } ?>
        <tr>
            <?php if($pfa != -1){ ?>
            <td class="border px-4 py-2"></td>
            <td class="border px-4 py-2"></td>
            <?php } ?>
            <td class="border px-4 py-2"></td>
            <td class="border px-4 py-2"></td>
            <td class="border px-4 py-2"><?php echo number_format($grossPension); ?></td>
        </tr>

        </tbody>
    </table>
</div>
</div>
<script type="text/javascript">
    var table = document.getElementById('table-search');
    Sorrtty(table)
</script>
