<?php
session_start();
require_once '../../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../views/admin/settings.php");
    exit();
}

$admin_id =$_SESSION['user_id'];
$action =$_POST['action'] ?? '';

function verifyAdminPassword($pdo, $admin_id,$input_password) {
    $stmt =$pdo->prepare("SELECT Password FROM `user` WHERE User_ID = :uid");
    $stmt->execute([':uid' =>$admin_id]);
    $hash =$stmt->fetchColumn();
    return $hash && password_verify($input_password,$hash);
}

try {
    if ($action === 'change_password') {
        $current_pwd =$_POST['current_password'] ?? '';
        $new_pwd =$_POST['new_password'] ?? '';
        $confirm_pwd =$_POST['confirm_password'] ?? '';

        if (empty($current_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
            throw new Exception("All password fields are required.");
        }
        if ($new_pwd !==$confirm_pwd) {
            throw new Exception("New passwords do not match.");
        }
        if (strlen($new_pwd) < 8) {
            throw new Exception("New password must be at least 8 characters.");
        }
        if (!verifyAdminPassword($pdo, $admin_id,$current_pwd)) {
            throw new Exception("Incorrect current password.");
        }

        $hashed = password_hash($new_pwd, PASSWORD_BCRYPT);
        $stmt =$pdo->prepare("UPDATE `user` SET Password = ? WHERE User_ID = ?");
        $stmt->execute([$hashed,$admin_id]);

        $_SESSION['settings_success'] = "Admin password updated successfully.";

    } elseif ($action === 'register_instructor') {$fname = trim($_POST['first_name'] ?? '');$lname = trim($_POST['last_name'] ?? '');$email = trim($_POST['email'] ?? '');$pwd = $_POST['default_password'] ?? '';$facility_id = !empty($_POST['facility_id']) ?$_POST['facility_id'] : null;
        $auth_password =$_POST['admin_auth_password'] ?? '';

        // Check if any required field is empty
        if (empty($fname) || empty($lname) || empty($email) || empty($pwd) || empty($facility_id) || empty($auth_password)) {
            throw new Exception("All instructor registration fields and admin authorization password are required.");
        }

        if (!verifyAdminPassword($pdo, $admin_id,$auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        $check =$pdo->prepare("SELECT User_ID FROM `user` WHERE Email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            throw new Exception("Email already registered.");
        }

        $pdo->beginTransaction();
        
        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        $stmt1 =$pdo->prepare("INSERT INTO `user` (First_Name, Last_Name, Email, Password, Role) VALUES (?, ?, ?, ?, 'Instructor')");
        $stmt1->execute([$fname,$lname, $email,$hash]);
        $instructor_id =$pdo->lastInsertId();
        
        $stmt2 =$pdo->prepare("INSERT INTO `instructor` (Instructor_ID, Facility_ID) VALUES (?, ?)");
        $stmt2->execute([$instructor_id,$facility_id]);
        
        $pdo->commit();$_SESSION['settings_success'] = "Instructor registered and assigned successfully.";
        
    } elseif ($action === 'insert_facility') {
        $name = trim($_POST['facility_name'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $cap =$_POST['capacity'] ?? '';
        $open =$_POST['open_time'] ?? '';
        $close =$_POST['close_time'] ?? '';
        $auth_password =$_POST['admin_auth_password'] ?? '';

        // Check if any required field is empty
        if (empty($name) || empty($loc) || $cap === '' || empty($open) || empty($close) || empty($auth_password)) {
            throw new Exception("All facility details and admin authorization password are required.");
        }

        if (!verifyAdminPassword($pdo, $admin_id,$auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        $stmt =$pdo->prepare("INSERT INTO `facility` (Facility_Name, Location, Capacity, Open_Time, Close_Time) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $loc, (int)$cap, $open,$close]);

        $_SESSION['settings_success'] = "New facility added successfully.";

    } elseif ($action === 'update_facility') {
        $fac_id =$_POST['facility_id'] ?? '';
        $name = trim($_POST['facility_name'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $cap =$_POST['capacity'] ?? '';
        $open =$_POST['open_time'] ?? '';
        $close =$_POST['close_time'] ?? '';
        $auth_password =$_POST['admin_auth_password'] ?? '';

        // Check if any required field is empty
        if (empty($fac_id) || empty($name) || empty($loc) || $cap === '' || empty($open) || empty($close) || empty($auth_password)) {
            throw new Exception("All facility update fields and admin authorization password are required.");
        }

        if (!verifyAdminPassword($pdo, $admin_id,$auth_password)) {
            throw new Exception("Authentication failed. Invalid admin password.");
        }

        $stmt =$pdo->prepare("UPDATE `facility` SET Facility_Name=?, Location=?, Capacity=?, Open_Time=?, Close_Time=? WHERE Facility_ID=?");
        $stmt->execute([$name,$loc, (int)$cap,$open, $close,$fac_id]);

        $_SESSION['settings_success'] = "Facility details updated successfully.";
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {$pdo->rollBack();
    }
    $_SESSION['settings_error'] =$e->getMessage();
}

header("Location: ../../views/admin/settings.php");
exit();