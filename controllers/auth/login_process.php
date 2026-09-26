<?php
// controllers/auth/login_process.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/auth/AuthModel.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/auth/login.php");
    exit();
}

if (isset($_SESSION['registration_success'])) {
    unset($_SESSION['registration_success']);
}

$identifier = trim($_POST['identifier'] ?? '');
$password   = $_POST['password'] ?? '';

if (empty($identifier) || empty($password)) {
    $_SESSION['error'] = "Please enter both Email/Reg No and Password.";
    header("Location: ../../views/auth/login.php");
    exit();
}

try {

    $authModel = new AuthModel($pdo);
    $user = $authModel->getUserByIdentifier($identifier);

    if ($user && password_verify($password, $user['Password'])) {
        
        // Status checks for Students
        if ($user['Role'] === 'Student') {
            if ($user['student_status'] === 'pending') {
                $_SESSION['error'] = "Your registration is currently under review by the Administrator. You will be notified via email once approved.";
                header("Location: ../../views/auth/login.php");
                exit();
            }

            if ($user['student_status'] === 'suspended') {
                $_SESSION['error'] = "Your student membership has been suspended. Please contact the Physical Education Department.";
                header("Location: ../../views/auth/login.php");
                exit();
            }
        }

        // Prevent session fixation
        session_unset();
        session_regenerate_id(true);

        // Session variables set for Header, Sidebar, and Profile
        $_SESSION['user_id']         = $user['User_ID'];
        $_SESSION['first_name']      = $user['First_Name'];
        $_SESSION['user_name']       = $user['full_name'];
        $_SESSION['full_name']       = $user['full_name'];
        $_SESSION['email']           = $user['Email'];
        $_SESSION['reg_no']          = $user['Registration_Number'] ?? 'Student';
        $_SESSION['profile_image']   = $user['Profile_Image'] ?? 'default_avatar.png';
        $_SESSION['life_percentage'] = (int)($user['Life_Percentage'] ?? 100);
        
        // Standardize internal session roles to lowercase
        $role_lower = strtolower($user['Role']);
        $_SESSION['role']       = ($role_lower === 'student') ? 'member' : $role_lower;
        $_SESSION['is_captain'] = ((int)$user['is_captain'] > 0) ? 1 : 0;

        // Role-based redirection
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: ../../views/admin/analytics.php");
                break;
            case 'instructor':
                header("Location: ../../views/instructor/kiosk.php");
                break;
            case 'member':
            default:
                header("Location: ../../views/member/dashboard.php");
                break;
        }
        exit();

    } else {
        $_SESSION['error'] = "Invalid credentials. Please verify your Institutional Email/Reg No and password.";
        header("Location: ../../views/auth/login.php");
        exit();
    }

} catch (\PDOException $e) {
    error_log("Login DB Failure: " . $e->getMessage());
    $_SESSION['error'] = "Authentication service is currently unavailable. Please try again shortly.";
    header("Location: ../../views/auth/login.php");
    exit();
}