<?php
// controllers/common/notifications_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Notifications & Announcements | FitCampus";
$user_id = $_SESSION['user_id'];

require_once '../../includes/db_connection.php';
require_once '../../models/common/NotificationModel.php';

$notificationModel = new NotificationModel($pdo);

$announcements = [];
$personal_notifications = [];

try {
    // Fetch Common Announcements via Model
    $announcements = $notificationModel->getAnnouncements();

    // Fetch Personal & Team Notifications via Model
    $personal_notifications = $notificationModel->getPersonalNotifications($user_id);

} catch (\PDOException $e) {
    error_log("Notifications Fetch Error: " . $e->getMessage());
}

// Role Identification Logic
$role = strtolower($_SESSION['role'] ?? '');
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>