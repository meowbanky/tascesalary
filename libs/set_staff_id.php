<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = $_POST['staff_id'];
    $_SESSION['staff'] = $staff_id;
    header("Location: ../empearnings.php");
    exit();
}
?>