<?php
// libs/middleware.php
function checkPermission($requiredPage) {
session_start();

// Check if user is logged in
if (!isset($_SESSION['SESS_MEMBER_ID'])) {
header('Location: login.php');
exit();
}

// Get user's role_id from session
$role_id = $_SESSION['role_id'];

// Check if the user has permission to access the required page
require 'App.php';
$App = new App();
$query = "SELECT COUNT(*) FROM permissions WHERE role_id = :role_id AND page = :page";
$params = [':role_id' => $role_id, ':page' => $requiredPage];
$hasPermission = $App->selectOne($query, $params);
if ($hasPermission['COUNT(*)'] == 0) {
header('Location: unauthorized.php');
exit();
}
}
?>