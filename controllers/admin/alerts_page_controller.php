<?php
// controllers/admin/alerts_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin Authorization Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$page_title = 'Staff Alerts & Broadcasts - FitCampus';

require_once '../../includes/db_connection.php';
require_once '../../models/admin/AlertModel.php';

$alertModel = new AlertModel($pdo);
$alerts = $alertModel->getSystemAlerts();
?>