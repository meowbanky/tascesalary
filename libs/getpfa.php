<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if(isset($_POST['search'])){
    $search = $_POST['search'];
    $pfas = $App->getPfaDetails($search);
}else{
    $pfas = $App->getPfaDetails();
}


?>