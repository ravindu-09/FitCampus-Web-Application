<?php
// controllers/instructor/kiosk_action.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connection.php';
require_once '../../models/instructor/KioskModel.php';

header('Content-Type: application/json');

// Check if user is authenticated and is an instructor
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$kioskModel = new KioskModel($pdo);
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Handle manual check-in action
if ($action === 'manual_checkin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no = trim($_POST['reg_no'] ?? '');
    
    if (empty($reg_no)) {
        echo json_encode(['success' => false, 'message' => 'Registration number is required']);
        exit();
    }

    try {
        $student = $kioskModel->getStudentByRegNo($reg_no);
        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit();
        }

        echo json_encode(['success' => true, 'message' => 'Student successfully checked in.']);
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// Handle life deduction action
if ($action === 'deduct_life' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $deduct_amount = (int)($_POST['amount'] ?? 0);

    if ($user_id <= 0 || $deduct_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }

    try {
        // Update life percentage in university_student table
        $stmt = $pdo->prepare("UPDATE university_student SET Life_Percentage = GREATEST(0, Life_Percentage - ?) WHERE User_ID = ?");
        $stmt->execute([$deduct_amount, $user_id]);

        echo json_encode(['success' => true, 'message' => 'Life percentage updated successfully.']);
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>