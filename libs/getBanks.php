<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if(isset($_POST['search'])){
    $search = $_POST['search'];
    $banks = $App->getBanksDetails($search);
}else{
    $banks = $App->getBanksDetails();
}


?>