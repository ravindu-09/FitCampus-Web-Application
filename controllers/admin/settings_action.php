<?php
// controllers/admin/settings_action.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/admin/SettingsModel.php'; // Include Model

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/admin/settings.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$settingsModel = new SettingsModel($pdo);

// Updated verification function using the model
function verifyAdminPassword($settingsModel, $admin_id, $input_password) {
    $hash = $settingsModel->getAdminPasswordHash($admin_id);
    return $hash && password_verify($input_password, $hash);
}

try {
    if ($action === 'change_password') {
        $current_pwd = $_POST['current_password'] ?? '';
        $new_pwd = $_POST['new_password'] ?? '';
        $confirm_pwd = $_POST['confirm_password'] ?? '';

        if (empty($current_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
            throw new Exception("All password fields are required.");
        }
        if ($new_pwd !== $confirm_pwd) {
            throw new Exception("New passwords do not match.");
        }
        if (strlen($new_pwd) < 8) {
            throw new Exception("New password must be at least 8 characters.");
        }
        if (!verifyAdminPassword($settingsModel, $admin_id, $current_pwd)) {
            throw new Exception("Incorrect current password.");
        }

        $hashed = password_hash($new_pwd, PASSWORD_BCRYPT);
        $settingsModel->updateAdminPassword($admin_id, $hashed);

        $_SESSION['settings_success'] = "Admin password updated successfully.";

    } elseif ($action === 'register_instructor') {
        $fname = trim($_POST['first_name'] ?? '');
        $lname = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pwd = $_POST['default_password'] ?? '';
        $facility_id = !empty($_POST['facility_id']) ? $_POST['facility_id'] : null;
        $auth_password = $_POST['admin_auth_password'] ?? '';

        if (empty($fname) || empty($lname) || empty($email) || empty($pwd) || empty($facility_id) || empty($auth_password)) {
            throw new Exception("All instructor registration fields and admin authorization password are required.");
        }

        if (!verifyAdminPassword($settingsModel, $admin_id, $auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        if ($settingsModel->checkEmailExists($email)) {
            throw new Exception("Email already registered.");
        }

        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        $settingsModel->registerInstructor($fname, $lname, $email, $hash, $facility_id);
        
        $_SESSION['settings_success'] = "Instructor registered and assigned successfully.";
        
    } elseif ($action === 'insert_facility') {
        $name = trim($_POST['facility_name'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $cap = $_POST['capacity'] ?? '';
        $open = $_POST['open_time'] ?? '';
        $close = $_POST['close_time'] ?? '';
        $auth_password = $_POST['admin_auth_password'] ?? '';

        if (empty($name) || empty($loc) || $cap === '' || empty($open) || empty($close) || empty($auth_password)) {
            throw new Exception("All facility details and admin authorization password are required.");
        }

        if (!verifyAdminPassword($settingsModel, $admin_id, $auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        $settingsModel->insertFacility($name, $loc, $cap, $open, $close);

        $_SESSION['settings_success'] = "New facility added successfully.";

    } elseif ($action === 'update_facility') {
        $fac_id = $_POST['facility_id'] ?? '';
        $name = trim($_POST['facility_name'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $cap = $_POST['capacity'] ?? '';
        $open = $_POST['open_time'] ?? '';
        $close = $_POST['close_time'] ?? '';
        $auth_password = $_POST['admin_auth_password'] ?? '';

        if (empty($fac_id) || empty($name) || empty($loc) || $cap === '' || empty($open) || empty($close) || empty($auth_password)) {
            throw new Exception("All facility update fields and admin authorization password are required.");
        }

        if (!verifyAdminPassword($settingsModel, $admin_id, $auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        $settingsModel->updateFacility($fac_id, $name, $loc, $cap, $open, $close);

        $_SESSION['settings_success'] = "Facility details updated successfully.";
    }

} catch (Exception $e) {
    // Transaction rollbacks are now handled within the Model
    $_SESSION['settings_error'] = $e->getMessage();
}

header("Location: ../../views/admin/settings.php");
exit();