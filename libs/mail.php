<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';
$APP = NEW App;


    $email = 'bankole.adesoji@gmail.com';

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
            $mail->SMTPDebug = 3; // Enable verbose debug output (0 = off, 1 = client messages, 2 = client and server messages)
            $mail->Debugoutput = 'html'; // Output format (html for browser)

            //Recipients
            $mail->setFrom('no-reply@tascesalary.com.ng', $_SESSION['businessname']);
            $mail->addAddress($email);

            // Attachments
//            $mail->addAttachment($filePath);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Payslip';
            $mail->Body    = 'Please find attached your payslip.';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        } finally {
            // Delete the generated file after sending

        }

?>
<?php
