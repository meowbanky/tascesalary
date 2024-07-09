<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';
$App = new App();
$App->checkAuthentication();
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

$App = new App();
if ($_SERVER['REQUEST_METHOD'] == 'GET') {


        if(isset($_GET['payperiod'])) {
            $period = $_GET['payperiod'];
        }

        if(isset($_GET['bank'])) {
            $bank = $_GET['bank'];
        }
        $GrossPays = $App->getBankSummary($period,$bank);



    $periodDescs = $App->getPeriodDescription($period);
     $periodDesc = $periodDescs['period'];

if($bank != -1){
    
}

    if($GrossPays ){
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header row
    $headers = ['S/N', 'NAME', 'AMOUNT', 'PAYMENT DATE','BEN CODE','ACCOUNT NO  3','BANK CODE','DEBIT ACCOUNT','BANK 3 (BANK SAVINGS)'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Apply bold style to header row
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);

    $sn=1;

        $descrip ='';
    // Fill allowance data rows
    $row = 2;
    foreach($GrossPays as $GrossPay)
        {
            $padded = str_pad($GrossPay['staff_id'],3,"0",0);
            $sm_padd = str_pad($sn,3,"0",0);
            $sn_des = 'TASCE_'.$periodDesc.' SAL_'.$sm_padd;
            $sheet->setCellValue('A' . $row,$sn_des);
            $sheet->setCellValue('B' . $row, $GrossPay['NAME']);
            $sheet->setCellValue('C' . $row, number_format($GrossPay['allow']-$GrossPay['deduc']));
            $sheet->setCellValue('D' . $row, date('d/m/Y'));
            $sheet->setCellValue('E' . $row, 'TASCE '.$padded);
            $sheet->setCellValue('F' . $row, $GrossPay['acctno']);
            $sheet->setCellValue('G' . $row, $GrossPay['bankcode']);
            $sheet->setCellValue('H' . $row, '0051719443');
            $sheet->setCellValue('I' . $row, $GrossPay['bankname']);
            $sn++;
            $row++;
        }


        // Generate Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'Net2bank'.'_' . $periodDesc . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
    }
} else {
    echo 'Invalid request. Please provide a valid period.';
}
?>
