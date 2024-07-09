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

    $salaryValue = $App->getSalaryValue($grade,$step,$salaryType,$allow_id);
    $basicSalary = $App->getSalaryValue($grade,$step,$salaryType,1);

     if($salaryValue){
             $response['salaryValue'] = $salaryValue['value'];
             $response['status'] = 'success';
     }else{
         if($allow_id == 25){
             if($basicSalary !== null){
                 $response['salaryValue'] = (int)(floatval($basicSalary['value']) * 0.075);
                 $response['status'] = 'success';
             }
         }else {

             $response['salaryValue'] = 0;
             $response['status'] = 'success';
         }
     }


  echo  json_encode($response);
}
