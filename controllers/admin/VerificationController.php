<?php
// controllers/admin/VerificationController.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../bll/admin/VerificationBLL.php';

// Admin Authorization Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../../views/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/verification.php");
    exit();
}

$verificationBLL = new VerificationBLL($pdo);

try {
    $successMessage = $verificationBLL->processVerification($_POST);
    $_SESSION['success'] = $successMessage;
} catch (Exception $e) {
    error_log("Verification Action Failure: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../../views/admin/verification.php");
exit();