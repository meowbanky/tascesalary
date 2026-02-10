<?php
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payslipContent = $_POST['payslip'];

    // Initialize dompdf with options
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);

    // Load HTML content
    $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; margin-bottom: 20px; }
                .section { margin-bottom: 20px; }
                .section h2 { font-size: 16px; border-bottom: 1px solid #000; padding-bottom: 5px; }
                .details, .allowances, .deductions { width: 100%; border-collapse: collapse; }
                .details td, .allowances td, .deductions td { border: 1px solid #000; padding: 5px; }
                .total { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Payslip for June 2024</h1>
            </div>
            <div class="section">
                <h2>Employee Details</h2>
                <table class="details">
                    <tr><td>Name:</td><td>Adeosun Iremide Hope</td></tr>
                    <tr><td>Staff No.:</td><td>2918</td></tr>
                    <tr><td>Dept:</td><td>HOUSE OFFICERS</td></tr>
                    <tr><td>Bank:</td><td>ZENITH BANK PLC</td></tr>
                    <tr><td>Acct No.:</td><td>2209167984</td></tr>
                    <tr><td>Consolidated:</td><td>01M/01</td></tr>
                </table>
            </div>
            <div class="section">
                <h2>Consolidated Salary</h2>
                <table class="allowances">
                    <tr><td>Consolidated Salary:</td><td>138,967</td></tr>
                </table>
            </div>
            <div class="section">
                <h2>Allowances</h2>
                <table class="allowances">
                    <tr><td>Salary Arrears:</td><td>38,783</td></tr>
                    <tr><td>Hazard 2:</td><td>32,000</td></tr>
                    <tr><td>Call Duty Allowance:</td><td>60,120</td></tr>
                    <tr><td>Teaching Allowance:</td><td>9,372</td></tr>
                    <tr class="total"><td>Total Allowance:</td><td>140,275</td></tr>
                </table>
            </div>
            <div class="section">
                <h2>Gross Salary</h2>
                <table class="allowances">
                    <tr><td>Gross Salary:</td><td>279,242</td></tr>
                </table>
            </div>
        </body>
        </html>
    ';

    $dompdf->loadHtml($html);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    $dompdf->stream("payslip.pdf", ["Attachment" => true]);
} else {
    echo 'Invalid request';
}
