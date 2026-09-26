<?php
// controllers/auth/LogoutController.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../bll/auth/AuthBLL.php'; // Ensure this file is included correctly

$authBLL = new AuthBLL($pdo);
$authBLL->terminateSession();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Location: ../../views/auth/login.php");
exit();
?>