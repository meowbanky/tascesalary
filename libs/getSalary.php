<?php
include 'App.php';

$response = [];
$App = new App;

if(isset($_POST['grade']) && isset($_POST['step']) && isset($_POST['salaryType']) && isset($_POST['allow_id']))
{
    $grade = $_POST['grade'];
    $step = $_POST['step'];
    $salaryType = $_POST['salaryType'];
    $allow_id = $_POST['allow_id'];

     $salaryValue = $App->getSalaryValue($grade,$step,$salaryType,$allow_id);
     if($salaryValue){
         $response['salaryValue'] = $salaryValue['value'];
         $response['status'] = 'success';
     }else{
         $response['salaryValue'] = 0;
         $response['status'] = 'success';
     }


  echo  json_encode($response);
}
