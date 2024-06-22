<?php
require '../vendor/autoload.php';
require '../config/config.php';
require '../libs/App.php';
$APP = NEW App;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payslipContent = $_POST['payslip'];

    // Path to save the generated PDF
    $pdfFilePath = '../payslips/payslip_' . time() . '.pdf';

    // Generate PDF using a library like TCPDF, FPDF, etc.
    // Here is an example using TCPDF

    require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->writeHTML($payslipContent);
    $pdf->Output($pdfFilePath, 'F');

    if (file_exists($pdfFilePath)) {
        echo json_encode(['filePath' => $pdfFilePath]);
    } else {
        echo json_encode(['error' => 'Failed to generate PDF']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
