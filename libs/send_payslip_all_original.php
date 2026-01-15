<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Tcpdf\Fpdi; // Correct namespace for FPDI
use setasign\Fpdi\PdfParser\StreamReader;

require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

$App = new App();

$App->initializeOffset();
// Fetch batch size and current offset
$batchSize = 50;
$offsetData = $App->getOffset();
$businessData = $App->getBusinessName();
$businessName = $businessData['business_name'];
$offset = $offsetData['last_offset'] ?? 0;
$delay = 6; // delay in seconds between emails

$periodData = $App->lastActivePeriod();
$period = $_POST['period'];
$desc = $App->getPeriodDescription($period);
$desciption = $desc['period'];
$staffList = $App->getStaffList($batchSize, $offset);

foreach ($staffList as $staff) {
    $email = $staff['email'];
    $staff_id = $staff['staff_id'];


    $paySlips = $App->getPaySlip($staff_id, $period);
    $employeePayslip = $App->getEmployeeDetailsPayslip($staff_id, $period);

    if ($paySlips === false || $employeePayslip === false) {
        continue; // Skip if no data found
    }

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

    // Load HTML content
    $html = "
    <html>
    <head>
        <style>
            body { 
                font-family: Arial, sans-serif;
                position: relative; 
                -webkit-print-color-adjust: exact;
            }
            .header { text-align: center; margin-bottom: 20px; }
            .section { margin-bottom: 10px; }
            .section h2 { font-size: 12px; border-bottom: 1px solid #000; padding-bottom: 2px; }
            .details, .allowances, .deductions { width: 100%; border-collapse: collapse; }
            .details td, .allowances td, .deductions td { border: 1px solid #000; padding: 2px; }
            .total { font-weight: bold; }
            .background-image { 
                position: absolute; 
                top: 0; 
                left: 0;
                width: 100%; 
                height: 100%; 
                z-index: -1; 
                opacity: 0.1; 
            }
        </style>
    </head>
    <body>
        <img src='$bgImageSrc' class='background-image' />
        <div class='header'>
            <table style='width: 100%;'>
                <tr>
                    <td style='width: 20%;'><img src='$bgImageSrcR' style='width: 100px;' /></td>
                    <td style='width: 60%; text-align: center;'>
                        <h2>" . $businessName . "</h2>  
                        <h3>PAYSLIP FOR THE MONTH OF " . $desciption . "</h3>
                    </td>
                    <td style='width: 20%; text-align: right;'><img src='$bgImageSrcL' style='width: 100px;' /></td>
                </tr>
            </table>
        </div>
        <div class='section'>
            <h2>Employee Details</h2>
            <table class='details'>
                <tr><td>Name:</td><td>" . $employeePayslip['NAME'] . "</td></tr>
                <tr><td>Staff No.:</td><td>" . $employeePayslip['OGNO'] . "</td></tr>
                <tr><td>TIN:</td><td>" . ($employeePayslip['TIN'] ?? '') . "</td></tr>
                <tr><td>Dept:</td><td>" . $employeePayslip['dept'] . "</td></tr>
                <tr><td>Bank:</td><td>" . $employeePayslip['BNAME'] . "</td></tr>
                <tr><td>Acct No.:</td><td>" . $employeePayslip['ACCTNO'] . "</td></tr>
                <tr><td>Grade/Step:</td><td>" . $employeePayslip['GRADE'] . "/" . $employeePayslip['STEP'] . "</td></tr>
                <tr><td>Salary Structure:</td><td>" . $employeePayslip['SalaryType'] . "</td></tr>
            </table>
        </div>";

    $html .= "<div class='section'>
            <h2>Allowances</h2>
            <table class='allowances'>";
    $gross = 0;
    foreach ($paySlips as $paySlip) {
        if ($paySlip['allow'] != 0) {
            $html .= "<tr><td>" . $paySlip['ed'] . "</td><td>" . number_format($paySlip['allow']) . "</td></tr>";
            $gross += $paySlip['allow'];
        }
    }
    $html .= "<tr class='total'><td>Gross Salary:</td><td>" . number_format($gross) . "</td></tr>
            </table>
        </div>
        <div class='section'>
            <h2>Deductions</h2>
            <table class='allowances'>";
    $Totaldeductions = 0;
    foreach ($paySlips as $paySlip) {
        if ($paySlip['deduc'] != 0) {
            $html .= "<tr><td>" . $paySlip['ed'] . "</td><td>" . number_format($paySlip['deduc']) . "</td></tr>";
            $Totaldeductions += $paySlip['deduc'];
        }
    }
    $html .= "<tr class='total'><td>Total Deductions:</td><td>" . number_format($Totaldeductions) . "</td></tr>
            </table>
        </div>
        <div class='section'>
            <h2>Net Pay</h2>
            <table class='allowances'>
                <tr class='total'><td>NET PAY:</td><td>" . number_format($gross - $Totaldeductions) . "</td></tr>
            </table>
        </div>
    </body>
    </html>";

    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $output = $dompdf->output();
    $tempFilePath = tempnam(sys_get_temp_dir(), 'payslip_') . '.pdf';
    file_put_contents($tempFilePath, $output);

    // Initialize TCPDF with FPDI
    $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($tempFilePath);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplId = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($tplId);
    }

    // Set protection: allow printing, but not modifications
    $pdf->SetProtection(['print'], '', 'securepassword', 0, null);

    $finalName = $desciption.'_'.$employeePayslip['NAME'].'_';
    $protectedFilePath = tempnam(sys_get_temp_dir(),  $finalName) . '.pdf';
    $pdf->Output($protectedFilePath, 'F');

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = HOST_MAIL; // Set the SMTP server to send through
//        $mail->Host = 'standard6.doveserver.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = USERNAME; // SMTP username
        $mail->Password = PASSWORD; // SMTP password
        $mail->SMTPSecure = SMTPSECURE ; //PHPMailer::ENCRYPTION_STARTTLS;
//        $mail->SMTPSecure = 'tls';
        $mail->Port = PORT;

        // Enable SMTP debugging
        $mail->SMTPDebug = SMTPDEBUG; // Enable verbose debug output (0 = off, 1 = client messages, 2 = client and server messages)
        $mail->Debugoutput = 'html'; // Output format

        //Recipients
        $mail->setFrom('no-reply@tascesalary.com.ng', $businessName);
        $mail->addAddress($email);
//        $mail->addBCC('bankole.adesoji@gmail.com','banky');

        // Attachments
        $mail->addAttachment($protectedFilePath);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $desciption.' Payslip';
        $mail->Body    = 'Dear '. $employeePayslip['NAME'].' Please find attached your payslip for the month of '.$desciption .
                         '. We hope that you find the information in the payslip accurate and helpful.<br><br> 
                           Please review your payslip and let us know if you have any questions or concerns. If you believe there is an error, please contact the Bursary department immediately so we can resolve the issue.<br>
                           Thank you for your hard work and dedication. <br>HOD Salary & Wages';

        $mail->send();
    } catch (Exception $e) {
        // Log error
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    } finally {
        // Delete the generated file after sending
        if (file_exists($protectedFilePath)) {
            unlink($protectedFilePath);
        }
    }

    sleep($delay); // Delay between sending emails
}

// Update offset
$App->updateOffset($offset + $batchSize);

echo 'Batch processed successfully';
?>