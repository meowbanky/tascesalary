<?php

require_once '../vendor/autoload.php';
require_once 'App.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$App = new App();
$App->checkAuthentication();


if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if(isset($_GET['payperiod'])) {
        $period = $_GET['payperiod'];
    }

    if(isset($_GET['allow_id'])){
        $allow_id = $_GET['allow_id'];
    }
    if(isset($_GET['type'])){
        $type = $_GET['type'];
    }

    if(isset($_GET['email'])){
        $email = $_GET['email'];
    }
    $businessData = $App->getBusinessName();
    $businessName = $businessData['business_name'];
    $Deductions = $App->getReportDeductionList($period, $type, $allow_id);
    $periodDescs = $App->getPeriodDescription($period);
    $periodDesc = $periodDescs['period'];

    $allow_deduc = $type == 1 ? 'Allowance' : 'Deduction';
    if ($Deductions) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = ['Allowance/Deduction', 'Staff No', 'Name', 'Amount'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Apply bold style to header row
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $gross = 0;
        $descrip = '';

        // Fill allowance data rows
        $row = 2;
        foreach ($Deductions as $Deduction) {
            $sheet->setCellValue('A' . $row, $Deduction['edDesc']);
            $sheet->setCellValue('B' . $row, $Deduction['OGNO']);
            $sheet->setCellValue('C' . $row, $Deduction['NAME']);
            $sheet->setCellValue('D' . $row, number_format($Deduction['value']));
            $gross += $Deduction['value'];
            $descrip = $Deduction['edDesc'];
            $row++;
        }

        // Add Gross total and apply bold style
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('D' . $row, number_format($gross));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);

        // Save the spreadsheet to a temporary file
        $writer = new Xlsx($spreadsheet);
        $filename = $descrip . '_' . $periodDesc . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), 'deductions') . '.xlsx';
        $writer->save($temp_file);

        // Send the email with PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
//            $mail->isSMTP();
//            $mail->Host = HOST_MAIL; // Set the SMTP server to send through
//            $mail->SMTPAuth = true;
//            $mail->Username = USERNAME; // SMTP username
//            $mail->Password = PASSWORD; // SMTP password
//            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//            $mail->Port = 587;


            $mail->SMTPDebug = SMTPDEBUG;
            $mail->isSMTP();
            $mail->Host       = HOST_MAIL;
            $mail->SMTPAuth   = true;
            $mail->Username   = USERNAME;
            $mail->Password   = PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = PORT;
            
            // Recipients
            $mail->setFrom(USERNAME,  $businessName);
            $mail->addAddress($email); // Add more recipients if needed
            // $mail->addAddress('recipient2@example.com');

            // Attachments
            $mail->addAttachment($temp_file, $filename); // Add the generated Excel file

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $descrip . '_' . $periodDesc .' Report';
            $mail->Body    = 'Please find the attached deductions '.$descrip . '_' . $periodDesc.' report.';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        // Delete the temporary file
        unlink($temp_file);
    } else {
        echo 'Invalid request. Please provide a valid period.';
    }
}
?>
