<?php
// controllers/admin/support_requests_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Administrator access required.');
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/SupportRequestModel.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = 'Support Requests | FitCampus Admin';
$supportModel = new SupportRequestModel($pdo);
$admin_requests = [];
$support_error = null;

try {
    $admin_requests = $supportModel->getAllRequests();
} catch (PDOException $e) {
    error_log('Admin support requests fetch error: ' . $e->getMessage());
    $support_error = 'Requests could not be loaded. Check that the support database migration has been applied.';
}
?>