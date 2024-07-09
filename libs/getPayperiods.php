<?php

require_once 'App.php';

$App = new App;

$payPeriodDatas = $App->getPayPeriod();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $perioddesc = $_POST['perioddesc'];
    $periodyear = $_POST['periodyear'];

    $result = $App->insertPeriod($perioddesc, $periodyear);
    if($result){
        echo 'period saved successfully';
    }else{
        echo 'Error saving';
    }
}


?>