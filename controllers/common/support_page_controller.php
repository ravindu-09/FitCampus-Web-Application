<?php
// controllers/common/support_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: ../../views/auth/login.php');
    exit();
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/SupportRequestModel.php';

$user_id = (int)$_SESSION['user_id'];
$role = strtolower($_SESSION['role'] ?? '');
$is_captain = !empty($_SESSION['is_captain']);
$page_title = 'Support | FitCampus';
$support_error = null;
$supportModel = new SupportRequestModel($pdo);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

try {
    $support_profile = $supportModel->getUserProfile($user_id);
    if (!$support_profile) {
        http_response_code(403);
        exit('Your account could not be verified. Please sign in again.');
    }
    $support_requests = $supportModel->getRequestsForUser($user_id);
} catch (PDOException $e) {
    error_log('Support page load error: ' . $e->getMessage());
    $support_profile = [];
    $support_requests = [];
    $support_error = 'Support is temporarily unavailable. Please try again later.';
}
?>