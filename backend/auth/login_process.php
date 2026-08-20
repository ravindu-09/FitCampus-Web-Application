<?php
session_start();
require_once '../../includes/db_connection.php';

// Form submission check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Clear any old registration success flash messages upon new login attempts
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
    // Lookup user by institutional email or reg_no
    $stmt = $pdo->prepare("
        SELECT user_id, full_name, email, password_hash, role, status, is_captain 
        FROM users 
        WHERE email = :id_email OR reg_no = :id_reg
        LIMIT 1
    ");
    $stmt->execute([
        ':id_email' => $identifier,
        ':id_reg'   => $identifier
    ]);
    
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        
        // Account verification check
        if ($user['status'] === 'pending') {
            $_SESSION['error'] = "Your registration is currently under review by the Administrator. You will be notified via email once approved.";
            header("Location: ../../views/auth/login.php");
            exit();
        }

        if ($user['status'] === 'rejected') {
            $_SESSION['error'] = "Your registration request was declined. Please check your email for further instructions or contact support.";
            header("Location: ../../views/auth/login.php");
            exit();
        }

        // Prevent session fixation
        session_unset();
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['full_name']  = $user['full_name'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['is_captain'] = (bool)$user['is_captain'];

        // Role-based redirection
        switch ($user['role']) {
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