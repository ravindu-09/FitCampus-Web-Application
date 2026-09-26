<?php
// controllers/admin/SettingController.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../bll/admin/SettingBLL.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/admin/settings.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$settingBLL = new SettingBLL($pdo);

try {
    if ($action === 'change_password') {
        $message = $settingBLL->changePassword(
            $admin_id, 
            $_POST['current_password'] ?? '', 
            $_POST['new_password'] ?? '', 
            $_POST['confirm_password'] ?? ''
        );
        $_SESSION['settings_success'] = $message;

    } elseif ($action === 'register_instructor') {
        $message = $settingBLL->registerInstructor($admin_id, $_POST);
        $_SESSION['settings_success'] = $message;

    } elseif ($action === 'insert_facility') {
        $message = $settingBLL->insertFacility($admin_id, $_POST);
        $_SESSION['settings_success'] = $message;

    } elseif ($action === 'update_facility') {
        $message = $settingBLL->updateFacility($admin_id, $_POST);
        $_SESSION['settings_success'] = $message;
    }

} catch (Exception $e) {
    $_SESSION['settings_error'] = $e->getMessage();
}

header("Location: ../../views/admin/settings.php");
exit();