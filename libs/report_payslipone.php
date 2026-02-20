<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';
$App = new App();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
    }

    $staff_id = $_POST['staff_id'];
    $period = $_POST['period'];

    $periodDescription = $App->getPeriodDescription($period);
    $periodDescription = $periodDescription['period'];

    $paySlips = $App->getPaySlip($staff_id,$period );
    $employeePayslip = $App->getEmployeeDetailsPayslip($staff_id, $period);

    // Debugging information
    if ($paySlips === false) {
        echo "Error: No payslip data found.";
        exit;
    }
    if ($employeePayslip === false) {
        echo "Error: No employee details found.";
        exit;
    }

    // Initialize dompdf with options
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);

    // Base64 encode the background images
    $bgImagePath = realpath('../assets/images/tasce_background.png');
    $bgImageData = base64_encode(file_get_contents($bgImagePath));
    $bgImageSrc = 'data:image/png;base64,' . $bgImageData;

    $bgImagePathR = realpath('../assets/images/ogun_logo.png');
    $bgImageDataR = base64_encode(file_get_contents($bgImagePathR));
    $bgImageSrcR = 'data:image/png;base64,' . $bgImageDataR;

    $bgImagePathL = realpath('../assets/images/tasce_r_logo.png');
    $bgImageDataL = base64_encode(file_get_contents($bgImagePathL));
    $bgImageSrcL = 'data:image/png;base64,' . $bgImageDataL;
    ?>
<style>
@media print {
    body * {
        visibility: hidden;
    }

    #payslipModal,
    #payslipModal * {
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

<div id="payslipModal" class="closemodal fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50">
    <div
        class="modal-content rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl w-full flex flex-col bg-white">
        <div class="scrollable-content px-6 py-4 flex-1 overflow-y-auto">
            <div class="px-6 py-4 flex justify-between items-center">
                <img src='<?php echo $bgImageSrcR ?>' style='width: 50px;' />
                <h4 class="text-md font-bold text-center"><?php echo $_SESSION['businessname'];?></h4>
                <img src='<?php echo $bgImageSrcL ?>' style='width: 50px;' />
            </div>

            <!-- Scrollable Content -->

            <div class="px-6 py-4">
                <h2 class="text-xl font-bold text-center">Payslip for <?php echo $periodDescription; ?></h2>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">Name:</span> <span><?php echo $employeePayslip['NAME'] ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">Staff No.:</span>
                <span><?php echo $employeePayslip['OGNO'] ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">TIN:</span> <span><?php echo $employeePayslip['TIN'] ?? ''; ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">Dept:</span> <span><?php echo $employeePayslip['dept'] ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">Bank:</span> <span><?php echo $employeePayslip['BNAME'] ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">Acct No.:</span> <span><?php echo $employeePayslip['ACCTNO']; ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">PFA/PEN:</span> <span><?php echo ($employeePayslip['PFANAME'] ?? '') . ' / ' . ($employeePayslip['PFAACCTNO'] ?? ''); ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">GRADE/STEP:</span>
                <span><?php echo $employeePayslip['GRADE'] ?>/<?php echo $employeePayslip['STEP'] ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-semibold">SALARY STRUCTURE:</span>
                <span><?php echo $employeePayslip['SalaryType']; ?></span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="font-semibold border border-black border-b w-full"></span>
            </div>
            <div class="mt-4">
                <h3 class="text-lg font-semibold">Allowances</h3>
                <?php $gross = 0;
                    $Totaldeductions = 0;
                    foreach($paySlips as $paySlip) {
                        if($paySlip['allow'] !=0){
                            ?>
                <div class="flex justify-between mb-1">
                    <span><?php echo $paySlip['ed']; ?>:</span>
                    <span><?php echo number_format($paySlip['allow']); ?></span>
                </div>
                <?php $gross = $gross +$paySlip['allow'];
                        }
                    }
                    ?>
                <div class="flex justify-between py-2 mb-2 font-bold border-y-2 border-black">
                    <span>Gross Salary:</span>
                    <span><?php echo number_format($gross);?></span>
                </div>
                <h3 class="text-lg font-semibold">Deductions</h3>
                <?php
                    foreach($paySlips as $paySlip) {
                        if($paySlip['deduc'] !=0){
                            ?>
                <div class="flex justify-between mb-1">
                    <span><?php echo $paySlip['ed']; ?>:</span>
                    <span><?php echo number_format($paySlip['deduc']); ?></span>
                </div>
                <?php $Totaldeductions = $Totaldeductions +$paySlip['deduc'];
                        }
                    }
                    ?>
                <div class="flex justify-between mb-2 font-bold border-y-2 border-black">
                    <span>Total Deductions:</span> <span><?php echo number_format($Totaldeductions);?></span>
                </div>
                <div class="flex justify-between mb-2 font-bold border-double border-black border-y-2">
                    <span>NET PAY:</span> <span><?php echo number_format($gross - $Totaldeductions)?></span>
                </div>
            </div>
        </div>

        <!-- Fixed Footer with Buttons -->
        <div class="bg-gray-50 px-4 py-3 flex flex-row-reverse gap-2">
            <button id="printButton" class="px-4 py-2 bg-red-600 text-white rounded"
                onclick="window.print()">Print</button>
            <button id="closeButton" type="button"
                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm closebutton">Close</button>
        </div>
    </div>
</div>
<?php
} else {
    echo 'Invalid request';
}
?>

<script>
// Hide the modal
document.querySelector('.closebutton').addEventListener('click', function() {
    document.querySelector('.closemodal').classList.add('hidden');
});
</script>