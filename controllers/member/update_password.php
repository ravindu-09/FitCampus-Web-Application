<?php
// backend/member/update_password.php
session_start();
require_once '../../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: ../../views/member/settings.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['settings_error'] = "All password fields are required.";
    header("Location: ../../views/member/settings.php");
    exit();
}

if ($new_password !== $confirm_password) {
    $_SESSION['settings_error'] = "New passwords do not match.";
    header("Location: ../../views/member/settings.php");
    exit();
}

if (strlen($new_password) < 8) {
    $_SESSION['settings_error'] = "New password must be at least 8 characters long.";
    header("Location: ../../views/member/settings.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT Password FROM `user` WHERE User_ID = :uid LIMIT 1");
    $stmt->execute([':uid' => $user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_password, $user['Password'])) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $pdo->prepare("UPDATE `user` SET Password = :pwd WHERE User_ID = :uid");
        $update_stmt->execute([
            ':pwd' => $hashed_password,
            ':uid' => $user_id
        ]);

        $_SESSION['settings_success'] = "Password updated successfully.";
    } else {
        $_SESSION['settings_error'] = "Your current password does not match our records.";
    }
} catch (\PDOException $e) {
    error_log("Password update error: " . $e->getMessage());
    $_SESSION['settings_error'] = "An error occurred while updating the password.";
}

header("Location: ../../views/member/settings.php");
exit();