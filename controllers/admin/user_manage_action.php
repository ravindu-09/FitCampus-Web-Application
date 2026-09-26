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
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid Security Token (CSRF)']);
    exit;
}

$action = $_POST['action'] ?? '';
$userModel = new UserModel($pdo);

// 1. UPDATE STUDENT META (Life % and Exam Date)
if ($action === 'update_meta') {
    $user_id = (int)$_POST['user_id'];
    $life_pct = (int)$_POST['life_percentage'];
    $exam_date = null;

    // Date validation
    if (!empty($_POST['final_exam'])) {
        $d = DateTime::createFromFormat('Y-m-d', $_POST['final_exam']);
        if ($d && $d->format('Y-m-d') === $_POST['final_exam']) {
            $exam_date = $_POST['final_exam'];
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid date format']);
            exit;
        }
    }

    try {
        $userModel->updateStudentMeta($user_id, $life_pct, $exam_date);
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