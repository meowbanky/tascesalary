<?php
include_once 'App.php';

$response = [];
$App = new App;

if(isset($_POST['grade']) && isset($_POST['step']) && isset($_POST['salaryType']) && isset($_POST['allow_id']))
{
    $grade = $_POST['grade'];
    $step = $_POST['step'];
    $salaryType = $_POST['salaryType'];
    $allow_id = $_POST['allow_id'];

    $dept_id = isset($_POST['dept_id']) ? $_POST['dept_id'] : -1;

    $salaryValue = $App->getSalaryValue($grade,$step,$salaryType,$allow_id);
    $basicSalary = $App->getSalaryValue($grade,$step,$salaryType,1);
    $extraPay = $App->getExtra($dept_id, $allow_id);
    $extraAmount = 0;
    if($extraPay) {
        $extraAmount = $extraPay['amount'];
    }
     if($salaryValue){
             $response['salaryValue'] = intval($salaryValue['value'])+intval($extraAmount);
             $response['status'] = 'success';
     }else{
         if($allow_id == 25){
             if (($basicSalary !== null) && ($basicSalary)) {
                     $response['salaryValue'] = (int)(floatval($basicSalary['value']) * 0.075);
                     $response['status'] = 'success';
                    }else{
                 $response['salaryValue'] = 0;
                 $response['status'] = 'success';
             }
         }else {
             $response['salaryValue'] = 0;
             $response['status'] = 'success';
         }
     }


  echo  json_encode($response);
}


