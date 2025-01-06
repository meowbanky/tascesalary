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
        $allow_id  =    $_POST['allow_id'];
    }
    if(isset($_POST['type'])){
        $type =    $_POST['type'];
    }

$allow_deduc = $type == 1 ? 'Allowance' : 'Deduction';

$Deductions = $App->getReportDeductionList($period, $type,$allow_id);

$allowDescription = $App->getAllowanceDescription($allow_id);
$allowDescription = $allowDescription['ed'];
}

if($allow_id == ""){
    echo "Select Deduction/allowance";
    exit();
}



?>
<style>
    @media print {
        body * {
            visibility: hidden; /* Hide everything in body */
        }
        #logoprint, #logoprint * {
            display: visible; /* Ensure logo is visible */
        }
        #payslipModal, #payslipModal * {
            visibility: visible; /* Ensure payslip content is visible */
        }
        #print-button {
            display: none; /* Hide the print button */
        }
    }
</style>
<div id="payslipModal" class="overflow-x-auto items-center  print:block ">
    <div id ="logoprint" class="flex justify-between items-center mb-4">
        <div class="flex items-center space-x-4">
            <img src="assets/images/ogun_logo.png" alt="Logo" class="w-16 h-16">
            <h1 class="text-2xl font-bold"<?php echo  $_SESSION['businessname']; ?></h1>
        </div>
        <div>

        </div>
        <div class="flex items-center space-x-4">
            <img src="assets/images/tasce_r_logo.png" alt="Logo" class="w-16 h-16">
        </div>
    </div>
    <h1 class="font-bold text-center uppercase"><?php echo $allow_deduc.': ' .$allowDescription; ?></h1>
    <table id="table-search" class="min-w-full bg-white print:block">
        <thead>
        <tr class="w-full bg-gray-200">
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">S/N</th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">Staff No</th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">Name</th>
            <th class="sortable px-4 py-2 text-left text-sm font-medium text-gray-700">Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php $gross=0; $sn= 1; $count=0; if($Deductions){ foreach($Deductions as $Deduction){ ?>
            <tr>
                <td class="border px-4 py-2"><?php echo $sn; ?></td>
                <td class="border px-4 py-2"><?php echo $Deduction['OGNO']; ?></td>
                <td class="border px-4 py-2"><?=($Deduction['NAME']) ?></td>
                <td class="border px-4 py-2"><?=number_format($Deduction['value']) ?></td>
            </tr>
        <?php $gross +=$Deduction['value'];$sn++;
        }
        } ?>
        <tr>
            <td class="border px-4 py-2 font-bold" colspan="3">Total</td>
            <td class="border px-4 py-2 font-bold"><?=number_format($gross); ?></td>
        </tr>

        </tbody>
    </table>

    <div class="bg-gray-50 px-4 py-3 flex flex-row-reverse gap-2">
        <button id="print-button" class="px-4 py-2 bg-red-600 text-white rounded print:hidden">Print</button>
    </div>

</div>
<script type="text/javascript">

    function printContent() {
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Content</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#table').html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }

    function printPayslip() {
        var printContents = document.getElementById('payslipModal').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }



    // Attach print function to the button click
    $('#print-button').click(function() {
        window.print()
    });
</script>
