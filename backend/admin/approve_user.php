<?php
// backend/admin/approve_user.php
session_start();
require_once '../../includes/db_connection.php';

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

$target_user_id = (int)($_POST['user_id'] ?? 0);
$action         = trim($_POST['action'] ?? '');
// Sanitize and resolve custom rejection reason with safe fallback
$user_provided_reason = trim($_POST['rejection_reason'] ?? '');
$default_reason       = 'Provided verification documents or academic details did not meet the required criteria.';
$rejection_note       = !empty($user_provided_reason) ? $user_provided_reason : $default_reason;

if ($target_user_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = "Invalid request parameters.";
    header("Location: ../../views/admin/verification.php");
    exit();
}

try {
    // 1. Fetch Target Applicant Details
    $stmt = $pdo->prepare("
        SELECT full_name, email, profile_image, id_front_image, id_back_image 
        FROM users 
        WHERE user_id = :id AND status = 'pending' 
        LIMIT 1
    ");
    $stmt->execute([':id' => $target_user_id]);
    $target_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$target_user) {
        $_SESSION['error'] = "Registration request not found or already processed.";
        header("Location: ../../views/admin/verification.php");
        exit();
    }

    $sender_email = "fitcampus.physicaledu.uoc.fake@gmail.com";
    $user_name    = $target_user['full_name'];
    $user_email   = $target_user['email'];

    $headers  = "From: FitCampus Physical Education <{$sender_email}>\r\n" .
                "Reply-To: {$sender_email}\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n" .
                "X-Mailer: PHP/" . phpversion();

    
    // ACTION 1: APPROVE USER
   
    if ($action === 'approve') {
        $update_stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE user_id = :id");
        $update_stmt->execute([':id' => $target_user_id]);

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

    
    // ACTION 2: REJECT & HARD DELETE (DB & FILES)
    
    } elseif ($action === 'reject') {
        $upload_dir = '../../assets/images/uploads/';

        // 1. Delete Uploaded Profile Image if not system default
        if (!empty($target_user['profile_image']) && $target_user['profile_image'] !== 'default_avatar.png' && file_exists($upload_dir . $target_user['profile_image'])) {
            @unlink($upload_dir . $target_user['profile_image']);
        }

        // 2. Delete Uploaded ID Front Card Image
        if (!empty($target_user['id_front_image']) && file_exists($upload_dir . $target_user['id_front_image'])) {
            @unlink($upload_dir . $target_user['id_front_image']);
        }

        // 3. Delete Uploaded ID Back Card Image
        if (!empty($target_user['id_back_image']) && file_exists($upload_dir . $target_user['id_back_image'])) {
            @unlink($upload_dir . $target_user['id_back_image']);
        }

        // 4. Permanently Delete Record from Database
        $delete_stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
        $delete_stmt->execute([':id' => $target_user_id]);

        // 5. Send Rejection Email Notice to Applicant
        $subject = "FitCampus - Registration Request Update";
        $message = "Dear {$user_name},\n\n" .
                   "We regret to inform you that your registration request for FitCampus could not be approved at this time.\n\n" .
                   "Reason for Rejection:\n" .
                   "{$rejection_note}\n\n" .
                   "If you believe this was an error, please re-register with valid documents or visit the Physical Education Department desk.\n\n" .
                   "Best Regards,\n" .
                   "Department of Physical Education\n" .
                   "University of Colombo";

        @mail($user_email, $subject, $message, $headers);
        $_SESSION['success'] = "Member registration declined, applicant completely removed from database, and notification email sent.";
    }

    header("Location: ../../views/admin/verification.php");
    exit();

} catch (\PDOException $e) {
    error_log("Admin Action DB Failure: " . $e->getMessage());
    $_SESSION['error'] = "A server error occurred while processing the request.";
    header("Location: ../../views/admin/verification.php");
    exit();
}