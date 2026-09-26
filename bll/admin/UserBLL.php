<?php
// controllers/admin/UserController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../includes/db_connection.php';
require_once '../../bll/admin/UserBLL.php';

header('Content-Type: application/json');

// Security Guard (Role verification)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// CSRF Protection Check
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid Security Token (CSRF)']);
    exit;
}

$action = $_POST['action'] ?? '';
$userBLL = new UserBLL($pdo);

try {
    if ($action === 'update_meta') {
        $userBLL->updateStudentMeta($_POST);
        echo json_encode(['success' => true]);
        exit;
    } 
    elseif ($action === 'promote_captain') {
        $userBLL->promoteCaptain($_POST);
        echo json_encode(['success' => true]);
        exit;
    } 
    elseif ($action === 'demote_captain') {
        $userBLL->demoteCaptain($_POST);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid Action']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}