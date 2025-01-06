<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function setSesstion()
{
    $_SESSION["user"] = true;
}

function logoutSesstion()
{
    $_SESSION["user"] = false;
}

function checkLogin(){
    if(!isset($_SESSION['logged_in']) AND ($_SESSION['logged_in'] != 1)){
        header('Location:index.php');
    }
}

