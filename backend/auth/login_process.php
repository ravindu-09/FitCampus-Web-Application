<?php
// backend/auth/login_process.php
session_start();
require_once '../../includes/db_connection.php';

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
    // Lookup user by Email or Registration Number with Captain status check
    $stmt = $pdo->prepare("
        SELECT 
            u.User_ID, 
            CONCAT(u.First_Name, ' ', u.Last_Name) AS full_name, 
            u.Email, 
            u.Password, 
            u.Role, 
            s.Status AS student_status,
            (SELECT COUNT(*) FROM `TEAM_MEMBER` tm WHERE tm.User_ID = u.User_ID AND tm.Role_In_Team = 'Captain') AS is_captain
        FROM `USER` u
        LEFT JOIN `UNIVERSITY_STUDENT` s ON u.User_ID = s.User_ID
        WHERE u.Email = :id_email OR s.Registration_Number = :id_reg
        LIMIT 1
    ");
    $stmt->execute([
        ':id_email' => $identifier,
        ':id_reg'   => $identifier
    ]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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

        $_SESSION['user_id']    = $user['User_ID'];
        $_SESSION['full_name']  = $user['full_name'];
        $_SESSION['email']      = $user['Email'];
        
        // Standardize internal session roles to lowercase
        $role_lower = strtolower($user['Role']);
        $_SESSION['role']       = ($role_lower === 'student') ? 'member' : $role_lower; // member, admin, instructor
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