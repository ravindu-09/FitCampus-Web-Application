<?php
// controllers/admin/approve_user.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/admin/VerificationModel.php'; // Include Model

// Load PHPMailer files directly
require_once '../../includes/PHPMailer/Exception.php';
require_once '../../includes/PHPMailer/SMTP.php';
require_once '../../includes/PHPMailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
$user_provided_reason = trim($_POST['rejection_reason'] ?? '');
$default_reason       = 'Provided verification documents or academic details did not meet the required criteria.';
$rejection_note       = !empty($user_provided_reason) ? $user_provided_reason : $default_reason;

if ($target_user_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = "Invalid request parameters.";
    header("Location: ../../views/admin/verification.php");
    exit();
}

try {
    $verificationModel = new VerificationModel($pdo);
    
    // Fetch Target Applicant Details using Model
    $target_user = $verificationModel->getTargetUserForApproval($target_user_id);

    if (!$target_user) {
        $_SESSION['error'] = "Registration request not found or already processed.";
        header("Location: ../../views/admin/verification.php");
        exit();
    }

    $user_name  = $target_user['full_name'];
    $user_email = $target_user['Email'];

    // ACTION 1: APPROVE USER
    if ($action === 'approve') {
        
        $verificationModel->approveUserStatus($target_user_id);

        $subject = "FitCampus - Registration Approved!";
        $message = "Dear {$user_name},\n\n" .
                   "Your registration request for the FitCampus Institutional Portal has been verified and APPROVED.\n" .
                   "You can now access your member dashboard, reserve workout slots, and view real-time facility updates.\n\n" .
                   "Sign In Portal: http://localhost/FitCampus-Web-Application/views/auth/login.php\n\n" .
                   "Best Regards,\n" .
                   "Department of Physical Education\n" .
                   "University of Colombo";

        sendFitCampusEmail($user_email, $user_name, $subject, $message);
        $_SESSION['success'] = "Member '{$user_name}' approved successfully and notification email sent.";

    // ACTION 2: REJECT & HARD DELETE
    } elseif ($action === 'reject') {
        $upload_dir = '../../assets/images/uploads/';

        // Delete profile image if uploaded
        if (!empty($target_user['profile_image']) && $target_user['profile_image'] !== 'default_avatar.png' && file_exists($upload_dir . $target_user['profile_image'])) {
            @unlink($upload_dir . $target_user['profile_image']);
        }
        // Delete registration photo if distinct
        if (!empty($target_user['reg_photo']) && $target_user['reg_photo'] !== $target_user['profile_image'] && file_exists($upload_dir . $target_user['reg_photo'])) {
            @unlink($upload_dir . $target_user['reg_photo']);
        }
        // Delete ID cards
        if (!empty($target_user['id_front_image']) && file_exists($upload_dir . $target_user['id_front_image'])) {
            @unlink($upload_dir . $target_user['id_front_image']);
        }
        if (!empty($target_user['id_back_image']) && file_exists($upload_dir . $target_user['id_back_image'])) {
            @unlink($upload_dir . $target_user['id_back_image']);
        }

        // Delete from database using Model
        $verificationModel->deleteRejectedUser($target_user_id);

        $subject = "FitCampus - Registration Request Update";
        $message = "Dear {$user_name},\n\n" .
                   "We regret to inform you that your registration request for FitCampus could not be approved at this time.\n\n" .
                   "Reason for Rejection:\n" .
                   "{$rejection_note}\n\n" .
                   "If you believe this was an error, please re-register with valid documents or visit the Physical Education Department desk.\n\n" .
                   "Best Regards,\n" .
                   "Department of Physical Education\n" .
                   "University of Colombo";

        sendFitCampusEmail($user_email, $user_name, $subject, $message);
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

/**
 * SMTP Mail Dispatcher via PHPMailer
 */
function sendFitCampusEmail($recipient_email, $recipient_name, $subject, $plain_body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fitcampus.physicaledu.uoc.fake@gmail.com';
        $mail->Password   = 'xrjmcrphjdsxombh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('fitcampus.physicaledu.uoc.fake@gmail.com', 'FitCampus Physical Education');
        $mail->addAddress($recipient_email, $recipient_name);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $plain_body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}
?>