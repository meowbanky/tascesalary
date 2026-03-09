<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $items = $App->get_earning_deductions($search);
} else {
    $items = $App->get_earning_deductions();
}
?>
