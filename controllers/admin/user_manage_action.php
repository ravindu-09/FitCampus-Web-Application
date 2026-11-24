<?php
// controllers/admin/user_manage_action.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/admin/UserModel.php'; // Include Model
header('Content-Type: application/json');

// Security Guard (Role verification)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// CSRF Protection Check
if (!is_string($_SESSION['csrf_token'] ?? null)
    || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid Security Token (CSRF)']);
    exit;
}

$action = $_POST['action'] ?? '';
$userModel = new UserModel($pdo);

// 1. UPDATE STUDENT META (Exam Date)
if ($action === 'update_meta') {
    $user_id = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    $exam_date = null;
    if ($user_id === false || $user_id < 1) {
        echo json_encode(['success' => false, 'error' => 'Invalid member account']);
        exit;
    }

    // Date validation
    $final_exam = $_POST['final_exam'] ?? '';
    if ($final_exam !== '' && !is_string($final_exam)) {
        echo json_encode(['success' => false, 'error' => 'Invalid date format']);
        exit;
    }
    if ($final_exam !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $final_exam);
        if ($d && $d->format('Y-m-d') === $final_exam) {
            $exam_date = $final_exam;
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid date format']);
            exit;
        }
    }

    try {
        $userModel->updateStudentMeta($user_id, $exam_date);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 2. PROMOTE TO CAPTAIN
if ($action === 'promote_captain') {
    $user_id = (int)$_POST['user_id'];
    $team_id = (int)$_POST['team_id'];

    try {
        $userModel->promoteToCaptain($user_id, $team_id);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 3. DEMOTE TO MEMBER
if ($action === 'demote_captain') {
    $user_id = (int)$_POST['user_id'];
    $team_id = (int)$_POST['team_id'];

    try {
        $userModel->demoteToMember($user_id, $team_id);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid Action']);
?>