<?php
session_start();
require_once '../../includes/db_connection.php';

// Check if user is logged in and has Admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../../views/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/verification.php");
    exit();
}

$target_user_id = (int)($_POST['user_id'] ?? 0);
$action         = trim($_POST['action'] ?? '');
$rejection_note = trim($_POST['rejection_reason'] ?? 'Provided verification documents or academic details did not meet the required criteria.');

if ($target_user_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = "Invalid request parameters.";
    header("Location: ../../views/admin/verification.php");
    exit();
}

try {
    // 1. Fetch target user's details & uploaded image filenames
    $stmt = $pdo->prepare("
        SELECT full_name, email, profile_image, id_front_image, id_back_image 
        FROM users 
        WHERE user_id = :id AND status = 'pending' 
        LIMIT 1
    ");
    $stmt->execute([':id' => $target_user_id]);
    $target_user = $stmt->fetch();

    if (!$target_user) {
        $_SESSION['error'] = "Registration request not found or already processed.";
        header("Location: ../../views/admin/verification.php");
        exit();
    }

    $sender_email = "fitcampus.physicaledu.uoc.fake@gmail.com";
    $user_name    = $target_user['full_name'];
    $user_email   = $target_user['email'];

    // Proper Email Headers for Gmail SMTP
    $headers  = "From: FitCampus Physical Education <{$sender_email}>\r\n" .
                "Reply-To: {$sender_email}\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n" .
                "X-Mailer: PHP/" . phpversion();

    if ($action === 'approve') {
        // 2. APPROVE ACTION: Set status to 'active'
        $update_stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE user_id = :id");
        $update_stmt->execute([':id' => $target_user_id]);

        // Send Approval Email
        $subject = "FitCampus - Registration Approved!";
        $message = "Dear {$user_name},\n\n" .
                   "Your registration request for the FitCampus Institutional Portal has been verified and APPROVED.\n" .
                   "You can now access your member dashboard, reserve workout slots, and view real-time facility updates.\n\n" .
                   "Sign In Portal: http://localhost/FitCampus-Web-Application/views/auth/login.php\n\n" .
                   "Best Regards,\n" .
                   "Department of Physical Education\n" .
                   "University of Colombo";

        @mail($user_email, $subject, $message, $headers);
        $_SESSION['success'] = "Member '{$user_name}' approved successfully and notification email sent.";

    } elseif ($action === 'reject') {
        // 3. REJECT ACTION: Delete uploaded files from server storage
        $upload_dir = '../../assets/images/uploads/';
        if (!empty($target_user['profile_image']) && file_exists($upload_dir . $target_user['profile_image'])) {
            @unlink($upload_dir . $target_user['profile_image']);
        }
        if (!empty($target_user['id_front_image']) && file_exists($upload_dir . $target_user['id_front_image'])) {
            @unlink($upload_dir . $target_user['id_front_image']);
        }
        if (!empty($target_user['id_back_image']) && file_exists($upload_dir . $target_user['id_back_image'])) {
            @unlink($upload_dir . $target_user['id_back_image']);
        }

        // Set status to 'rejected' and reset image columns to NULL
        $update_stmt = $pdo->prepare("
            UPDATE users 
            SET status = 'rejected', profile_image = NULL, id_front_image = NULL, id_back_image = NULL 
            WHERE user_id = :id
        ");
        $update_stmt->execute([':id' => $target_user_id]);

        // Send Rejection Email
        $subject = "FitCampus - Registration Request Update";
        $message = "Dear {$user_name},\n\n" .
                   "We regret to inform you that your registration request for FitCampus could not be approved at this time.\n\n" .
                   "Reason for Rejection:\n" .
                   "{$rejection_note}\n\n" .
                   "If you believe this was an error, please visit the Physical Education Department desk with your valid Student ID.\n\n" .
                   "Best Regards,\n" .
                   "Department of Physical Education\n" .
                   "University of Colombo";

        @mail($user_email, $subject, $message, $headers);
        $_SESSION['success'] = "Member registration declined, notification email sent, and uploaded storage cleaned up.";
    }

    header("Location: ../../views/admin/verification.php");
    exit();

} catch (\PDOException $e) {
    error_log("Admin Action DB Failure: " . $e->getMessage());
    $_SESSION['error'] = "A server error occurred while processing the request.";
    header("Location: ../../views/admin/verification.php");
    exit();
}