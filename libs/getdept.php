<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if(isset($_POST['search'])){
    $search = $_POST['search'];
    $depts = $App->getDeptDetails($search);
}else{
    $depts = $App->getDeptDetails();
}


?>