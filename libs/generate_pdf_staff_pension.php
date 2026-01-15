<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

$App = new App();
$App->checkAuthentication();

class StaffPensionPDF extends TCPDF
{
    protected $printedBy = '';
    protected $currentDate = '';

    public function setFooterMeta($printedBy, $currentDate)
    {
        $this->printedBy = $printedBy !== null ? (string)$printedBy : '';
        $this->currentDate = $currentDate !== null ? (string)$currentDate : '';
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(90, 10, 'Date Printed: ' . $this->currentDate, 0, 0, 'L');
        $this->Cell(90, 10, 'Printed By: ' . $this->printedBy, 0, 0, 'R');
    }
}

if (!isset($_GET['staff_id'], $_GET['period_from'], $_GET['period_to'])) {
    http_response_code(400);
    exit('Invalid request.');
}

$staffId = $_GET['staff_id'];
$periodFrom = $_GET['period_from'];
$periodTo = $_GET['period_to'];

if (!ctype_digit((string)$staffId) || !ctype_digit((string)$periodFrom) || !ctype_digit((string)$periodTo)) {
    http_response_code(400);
    exit('Invalid parameters.');
}

$staffId = (int)$staffId;
$periodFrom = (int)$periodFrom;
$periodTo = (int)$periodTo;

if ($periodFrom > $periodTo) {
    http_response_code(400);
    exit('Invalid period range.');
}

$profile = $App->getStaffProfile($staffId);
$history = $App->getStaffPensionHistory($staffId, $periodFrom, $periodTo);

if (!$profile) {
    http_response_code(404);
    exit('Staff not found.');
}

if (!$history) {
    http_response_code(404);
    exit('No pension data for the selected range.');
}

$periodFromDesc = $App->getPeriodDescription($periodFrom);
$periodToDesc = $App->getPeriodDescription($periodTo);

$periodRangeLabel = sprintf(
    '%s to %s',
    $periodFromDesc['period'] ?? $periodFrom,
    $periodToDesc['period'] ?? $periodTo
);

$businessInfo = $App->getBusinessName();
$businessName = $businessInfo['business_name'] ?? '';
$businessName = str_replace(',', ",\n", $businessName);

$userDetails = $App->getUsersDetails($_SESSION['SESS_MEMBER_ID']);
$printedBy = $userDetails['NAME'] ?? 'Unknown User';

$pdf = new StaffPensionPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($printedBy);
$pdf->SetTitle('Staff Pension History');
$pdf->SetSubject('Staff Pension History Report');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->setFooterMeta($printedBy, date('Y-m-d H:i:s'));

$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

$logoLeft = '../assets/images/ogun_logo.png';
$logoRight = '../assets/images/tasce_r_logo.png';

if (file_exists($logoLeft)) {
    $pdf->Image($logoLeft, 15, 10, 20);
}
if (file_exists($logoRight)) {
    $pdf->Image($logoRight, 175, 10, 20);
}

$pdf->SetFont('helvetica', 'B', 11);
$pdf->MultiCell(0, 10, $businessName, 0, 'C', 0, 1, '', '', true);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Staff Pension History', 0, 1, 'C');
$pdf->Cell(0, 6, 'Period Range: ' . $periodRangeLabel, 0, 1, 'C');
$pdf->Ln(4);

$pdf->SetFont('helvetica', '', 9);

$profileHtml = '<table cellpadding="4" cellspacing="0" border="0">
    <tr>
        <td width="50%"><strong>Staff Name:</strong> ' . htmlspecialchars($profile['NAME'] ?? '', ENT_QUOTES) . '</td>
        <td width="50%"><strong>Staff No:</strong> ' . htmlspecialchars($profile['staff_id'] ?? '', ENT_QUOTES) . '</td>
    </tr>
    <tr>
        <td width="50%"><strong>OGNO:</strong> ' . htmlspecialchars($profile['OGNO'] ?? '', ENT_QUOTES) . '</td>
        <td width="50%"><strong>PFA:</strong> ' . htmlspecialchars($profile['PFANAME'] ?? '', ENT_QUOTES) . '</td>
    </tr>
    <tr>
        <td width="50%"><strong>PFA Code:</strong> ' . htmlspecialchars($profile['PFACODE'] ?? '', ENT_QUOTES) . '</td>
        <td width="50%"><strong>PFA PIN:</strong> ' . htmlspecialchars($profile['PFAACCTNO'] ?? '', ENT_QUOTES) . '</td>
    </tr>
</table>';

$pdf->writeHTML($profileHtml, true, false, false, false, '');
$pdf->Ln(3);

$tableHtml = '<table border="1" cellpadding="4" cellspacing="0">
    <thead>
        <tr style="background-color:#f0f0f0;">
            <th width="10%"><strong>#</strong></th>
            <th width="55%"><strong>Period</strong></th>
            <th width="35%" align="right"><strong>Amount (₦)</strong></th>
        </tr>
    </thead>
    <tbody>';

$total = 0;
$sn = 1;

foreach ($history as $row) {
    $amount = (float)($row['amount'] ?? 0);
    $total += $amount;
    $tableHtml .= sprintf(
        '<tr>
            <td>%d</td>
            <td>%s</td>
            <td align="right">%s</td>
        </tr>',
        $sn++,
        htmlspecialchars($row['period_name'] ?? $row['period'], ENT_QUOTES),
        number_format($amount, 2)
    );
}

$tableHtml .= sprintf(
    '<tr style="background-color:#f0f0f0;">
        <td colspan="2"><strong>Total</strong></td>
        <td align="right"><strong>%s</strong></td>
    </tr>',
    number_format($total, 2)
);

$tableHtml .= '</tbody></table>';

$pdf->writeHTML($tableHtml, true, false, false, false, '');

$filename = sprintf(
    'Staff_Pension_%s_%s.pdf',
    preg_replace('/\s+/', '_', $profile['staff_id'] ?? 'staff'),
    date('Ymd_His')
);

$pdf->Output($filename, 'D');
exit;