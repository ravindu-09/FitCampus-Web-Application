<?php
// controllers/admin/analytics_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin Authorization Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$page_title = 'Executive Analytics Dashboard - FitCampus';

require_once '../../includes/db_connection.php';
require_once '../../models/admin/AnalyticsModel.php';

$analyticsModel = new AnalyticsModel($pdo);
$analytics_data = $analyticsModel->getAnalyticsData();
?>