<?php
// controllers/member/update_password.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/member/SettingsModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: ../../views/member/settings.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$settingsModel = new SettingsModel($pdo);

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
    $user = $settingsModel->getUserPasswordHash($user_id);

    if ($user && password_verify($current_password, $user['Password'])) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $settingsModel->updateUserPassword($user_id, $hashed_password);

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