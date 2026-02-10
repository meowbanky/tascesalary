<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Tcpdf\Fpdi;
require_once 'PayslipControl.php';

class PayslipMailer {
    private $app;
    private $batchSize;
    private $delay;
    private $businessName;
    private $period;
    private $periodDescription;

    public function __construct(App $app, int $batchSize = 1, int $delay = 6) {
        $this->app = $app;
        $this->batchSize = $batchSize;
        $this->delay = $delay;

        $businessData = $this->app->getBusinessName();
        $this->businessName = $businessData['business_name'];
    }

    public function setPeriod(string $period): void {
        $this->period = $period;
        $desc = $this->app->getPeriodDescription($period);
        $this->periodDescription = $desc['period'];
    }
    private function validateEmail(string $email): bool {
        // Remove any whitespace
        $email = trim($email);

        // Check if email is empty
        if (empty($email)) {
            error_log("Empty email address provided");
            return false;
        }

        // Check email format using PHP's built-in validator
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid email format: $email");
            return false;
        }

        // Additional checks for basic email requirements
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        if (!preg_match($pattern, $email)) {
            error_log("Email fails additional validation checks: $email");
            return false;
        }

        return true;
    }
    private function generatePDF(array $employeePayslip, array $paySlips): string {
        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            // Load and encode images once
            $images = $this->loadBackgroundImages();

            $html = $this->generateHTML($employeePayslip, $paySlips, $images);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Create temporary file with proper extension
            $tempFile = $this->createTempFile('initial_', '.pdf');
            file_put_contents($tempFile, $dompdf->output());

            // Add PDF protection
            $protectedPdf = $this->addPDFProtection($tempFile, $employeePayslip['NAME']);

            unlink($tempFile); // Clean up initial PDF

            return $protectedPdf;

        } catch (Exception $e) {
            error_log("PDF Generation failed: " . $e->getMessage());
            throw new RuntimeException("Failed to generate PDF", 0, $e);
        }
    }

    private function generateHTML(array $employeePayslip, array $paySlips, array $images): string {
        // Calculate totals
        $gross = 0;
        $totalDeductions = 0;
        foreach ($paySlips as $paySlip) {
            $gross += $paySlip['allow'];
            $totalDeductions += $paySlip['deduc'];
        }

        return <<<HTML
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
            <img src='{$images['bg']}' class='background-image' />
            <div class='header'>
                <table style='width: 100%;'>
                    <tr>
                        <td style='width: 20%;'><img src='{$images['right']}' style='width: 100px;' /></td>
                        <td style='width: 60%; text-align: center;'>
                            <h2>{$this->businessName}</h2>  
                            <h3>PAYSLIP FOR THE MONTH OF {$this->periodDescription}</h3>
                        </td>
                        <td style='width: 20%; text-align: right;'><img src='{$images['left']}' style='width: 100px;' /></td>
                    </tr>
                </table>
            </div>
            <div class='section'>
                <h2>Employee Details</h2>
                <table class='details'>
                    <tr><td>Name:</td><td>{$employeePayslip['NAME']}</td></tr>
                    <tr><td>Staff No.:</td><td>{$employeePayslip['OGNO']}</td></tr>
                    <tr><td>TIN:</td><td>{$employeePayslip['TIN']}</td></tr>
                    <tr><td>Dept:</td><td>{$employeePayslip['dept']}</td></tr>
                    <tr><td>Bank:</td><td>{$employeePayslip['BNAME']}</td></tr>
                    <tr><td>Acct No.:</td><td>{$employeePayslip['ACCTNO']}</td></tr>
                    <tr><td>Grade/Step:</td><td>{$employeePayslip['GRADE']}/{$employeePayslip['STEP']}</td></tr>
                    <tr><td>Salary Structure:</td><td>{$employeePayslip['SalaryType']}</td></tr>
                </table>
            </div>
            <div class='section'>
                <h2>Allowances</h2>
                <table class='allowances'>
                    {$this->generateAllowancesRows($paySlips)}
                    <tr class='total'><td>Gross Salary:</td><td>{$this->formatCurrency($gross)}</td></tr>
                </table>
            </div>
            <div class='section'>
                <h2>Deductions</h2>
                <table class='allowances'>
                    {$this->generateDeductionsRows($paySlips)}
                    <tr class='total'><td>Total Deductions:</td><td>{$this->formatCurrency($totalDeductions)}</td></tr>
                </table>
            </div>
            <div class='section'>
                <h2>Net Pay</h2>
                <table class='allowances'>
                    <tr class='total'><td>NET PAY:</td><td>{$this->formatCurrency($gross - $totalDeductions)}</td></tr>
                </table>
            </div>
        </body>
        </html>
        HTML;
    }

    private function generateAllowancesRows(array $paySlips): string {
        $rows = '';
        foreach ($paySlips as $paySlip) {
            if ($paySlip['allow'] != 0) {
                $amount = $this->formatCurrency($paySlip['allow']);
                $rows .= "<tr><td>{$paySlip['ed']}</td><td>{$amount}</td></tr>";
            }
        }
        return $rows;
    }

    private function generateDeductionsRows(array $paySlips): string {
        $rows = '';
        foreach ($paySlips as $paySlip) {
            if ($paySlip['deduc'] != 0) {
                $amount = $this->formatCurrency($paySlip['deduc']);
                $rows .= "<tr><td>{$paySlip['ed']}</td><td>{$amount}</td></tr>";
            }
        }
        return $rows;
    }

    private function formatCurrency(float $amount): string {
        return number_format($amount);
    }
    private function loadBackgroundImages(): array {
        $images = [];
        $imageFiles = [
            'bg' => '../assets/images/tasce_background.png',
            'right' => '../assets/images/ogun_logo.png',
            'left' => '../assets/images/tasce_r_logo.png'
        ];

        foreach ($imageFiles as $key => $path) {
            $realPath = realpath($path);
            if ($realPath === false || !is_readable($realPath)) {
                throw new RuntimeException("Cannot read image file: $path");
            }
            $images[$key] = 'data:image/png;base64,' . base64_encode(file_get_contents($realPath));
        }

        return $images;
    }

    private function createTempFile(string $prefix, string $suffix): string {
        $tempFile = tempnam(sys_get_temp_dir(), $prefix);
        if ($tempFile === false) {
            throw new RuntimeException("Failed to create temporary file");
        }

        $newPath = $tempFile . $suffix;
        if (!rename($tempFile, $newPath)) {
            unlink($tempFile);
            throw new RuntimeException("Failed to rename temporary file");
        }

        return $newPath;
    }

    private function addPDFProtection(string $sourcePath, string $employeeName): string {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($sourcePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($tplId);
        }

        // Set protection with a random password
        $password = bin2hex(random_bytes(16));
        $pdf->SetProtection(['print'], '', $password, 0, null);

        $protectedFile = $this->createTempFile(
            $this->periodDescription . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $employeeName) . '_',
            '.pdf'
        );

        $pdf->Output($protectedFile, 'F');
        return $protectedFile;
    }

    private function sendEmail(string $email, string $employeeName, string $pdfPath): void {

        if (!$this->validateEmail($email)) {
            throw new RuntimeException("Invalid email address: $email");
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = HOST_MAIL;
            $mail->SMTPAuth = true;
            $mail->Username = USERNAME;
            $mail->Password = PASSWORD;
            $mail->SMTPSecure = SMTPSECURE;
            $mail->Port = PORT;
            $mail->SMTPDebug = SMTPDEBUG;
            $mail->Debugoutput = 'html';

            // Recipients
            $mail->setFrom('no-reply@tascesalary.com.ng', $this->businessName);
            $mail->addAddress($email);

            // Attachments
            $mail->addAttachment($pdfPath);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $this->periodDescription . ' Payslip';
            $mail->Body = $this->getEmailBody($employeeName);

            $mail->send();

        } catch (Exception $e) {
            error_log("Email sending failed for $email: {$mail->ErrorInfo}");
            throw new RuntimeException("Failed to send email", 0, $e);
        }
    }

    public function processBatch(): void {
        $offset = $this->app->getOffset()['last_offset'] ?? 0;
        $control = new PayslipControl($this->app);

        if (!$control->shouldContinue()) {
            echo 'Process is stopped';
            return;
        }
        $staffList = $this->app->getStaffList($this->batchSize, $offset);

        foreach ($staffList as $staff) {
            if (empty($staff['email']) || !$this->validateEmail($staff['email'])) {
                error_log("Skipping staff_id {$staff['staff_id']}: Invalid/empty email address");
                continue;
            }
            try {
                $paySlips = $this->app->getPaySlip($staff['staff_id'], $this->period);
                $employeePayslip = $this->app->getEmployeeDetailsPayslip($staff['staff_id'], $this->period);

                if ($paySlips === false || $employeePayslip === false) {
                    continue;
                }

                $pdfPath = $this->generatePDF($employeePayslip, $paySlips);

                try {
                    $this->sendEmail($staff['email'], $employeePayslip['NAME'], $pdfPath);
                    sleep($this->delay);
                } finally {
                    if (file_exists($pdfPath)) {
                        unlink($pdfPath);
                    }
                }

            } catch (Exception $e) {
                error_log("Failed to process payslip for staff_id {$staff['staff_id']}: " . $e->getMessage());
                continue; // Continue with next employee
            }
        }

        $this->app->updateOffset($offset + $this->batchSize);
    }

    private function getEmailBody(string $employeeName): string {
        return <<<EOT
            Dear {$employeeName},
            
            Please find attached your payslip for the month of {$this->periodDescription}.
            We hope that you find the information in the payslip accurate and helpful.
            
            Please review your payslip and let us know if you have any questions or concerns. 
            If you believe there is an error, please contact the Bursary department immediately 
            so we can resolve the issue.
            
            Thank you for your hard work and dedication.
            
            HOD Salary & Wages
        EOT;
    }
}
?>