<?php
// controllers/admin/support_request_action.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Administrator access required.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['support_admin_error'] = 'The request could not be updated. Reload the page and try again.';
    header('Location: ../../views/admin/support_requests.php');
    exit();
}

$requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';
$response = trim($_POST['response'] ?? '');
$allowedStatuses = ['In Progress', 'Resolved', 'Rejected'];

if (!$requestId || !in_array($status, $allowedStatuses, true) || $response === '' || mb_strlen($response) > 5000) {
    $_SESSION['support_admin_error'] = 'Choose a valid status and enter a response of no more than 5,000 characters.';
    header('Location: ../../views/admin/support_requests.php');
    exit();
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/SupportRequestModel.php';

try {
    $supportModel = new SupportRequestModel($pdo);
    if ($supportModel->respondToRequest($requestId, (int)$_SESSION['user_id'], $status, $response)) {
        $_SESSION['support_admin_success'] = 'The request response has been saved.';
    } else {
        $_SESSION['support_admin_error'] = 'No request was updated. It may have been removed.';
    }
} catch (Throwable $e) {
    error_log('Admin support response error: ' . $e->getMessage());
    $_SESSION['support_admin_error'] = 'The response could not be saved. Please try again.';
}

header('Location: ../../views/admin/support_requests.php');
exit();
?>