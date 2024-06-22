<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

require '../vendor/autoload.php';
require '../config/config.php';
require '../libs/App.php';
$App = new App();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $staff_id = $_POST['staff_id'];

    $paySlips = $App->getPaySlip($staff_id, $_SESSION['currentactiveperiod']);
    $employeePayslip = $App->getEmployeeDetailsPayslip($staff_id, $_SESSION['currentactiveperiod']);

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

    // Base64 encode the background image
    $bgImagePath = realpath('../assets/images/tasce_background.png');
    $bgImageData = base64_encode(file_get_contents($bgImagePath));
    $bgImageSrc = 'data:image/png;base64,' . $bgImageData;

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
            <h2>" . $_SESSION['businessname'] . "</h2>  
            <h3>PAYSLIP FOR THE MONTH OF " . $_SESSION['activeperiodDescription'] . "</h3>
        </div>
        <div class='section'>
            <h2>Employee Details</h2>
            <table class='details'>
                <tr><td>Name:</td><td>" . $employeePayslip['NAME'] . "</td></tr>
                <tr><td>Staff No.:</td><td>" . $employeePayslip['staff_id'] . "</td></tr>
                <tr><td>Dept:</td><td>" . $employeePayslip['dept'] . "</td></tr>
                <tr><td>Bank:</td><td>" . $employeePayslip['BNAME'] . "</td></tr>
                <tr><td>Acct No.:</td><td>" . $employeePayslip['ACCTNO'] . "</td></tr>
                <tr><td>Grade/Step:</td><td>" . $employeePayslip['GRADE'] . "/" . $employeePayslip['STEP'] . "</td></tr>
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
    $html .= "<tr class='total'><td>Total Allowance:</td><td>" . number_format($gross) . "</td></tr>
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
                <tr><td>NET PAY:</td><td>" . number_format($gross - $Totaldeductions) . "</td></tr>
            </table>
        </div>
    </body>
    </html>";

    $dompdf->loadHtml($html);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Save the PDF to a file
    $output = $dompdf->output();
    $filePath = tempnam(sys_get_temp_dir(), 'payslip_') . '.pdf';
    file_put_contents($filePath, $output);

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = HOST_MAIL; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = USERNAME; // SMTP username
        $mail->Password = PASSWORD; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Enable SMTP debugging
        $mail->SMTPDebug = 0; // Enable verbose debug output (0 = off, 1 = client messages, 2 = client and server messages)
        $mail->Debugoutput = 'html'; // Output format (html for browser)

        //Recipients
        $mail->setFrom('no-reply@tascesalary.com.ng', $_SESSION['businessname']);
        $mail->addAddress($email);

        // Attachments
        $mail->addAttachment($filePath);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $_SESSION['activeperiodDescription'].' Payslip';
        $mail->Body    = 'Dear '. $employeePayslip['NAME'].' Please find attached your payslip for the month of '.$_SESSION['activeperiodDescription'];

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    } finally {
        // Delete the generated file after sending
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
} else {
    echo 'Invalid request';
}
?>
