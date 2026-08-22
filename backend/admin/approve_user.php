<?php
// backend/admin/approve_user.php
session_start();
require_once '../../includes/db_connection.php';

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
    // 1. Fetch Target Applicant Details
    $stmt = $pdo->prepare("
        SELECT 
            u.User_ID, 
            CONCAT(u.First_Name, ' ', u.Last_Name) AS full_name, 
            u.Email, 
            s.Profile_Image AS profile_image, 
            s.Student_ID_Front AS id_front_image, 
            s.Student_ID_Back AS id_back_image 
        FROM `USER` u
        INNER JOIN `UNIVERSITY_STUDENT` s ON u.User_ID = s.User_ID 
        WHERE u.User_ID = :id AND s.Status = 'pending' 
        LIMIT 1
    ");
    $stmt->execute([':id' => $target_user_id]);
    $target_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$target_user) {
        $_SESSION['error'] = "Registration request not found or already processed.";
        header("Location: ../../views/admin/verification.php");
        exit();
    }

    $user_name  = $target_user['full_name'];
    $user_email = $target_user['Email'];

    // ACTION 1: APPROVE USER
    if ($action === 'approve') {
        $update_stmt = $pdo->prepare("UPDATE `UNIVERSITY_STUDENT` SET `Status` = 'active' WHERE `User_ID` = :id");
        $update_stmt->execute([':id' => $target_user_id]);

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

        if (!empty($target_user['profile_image']) && $target_user['profile_image'] !== 'default_avatar.png' && file_exists($upload_dir . $target_user['profile_image'])) {
            @unlink($upload_dir . $target_user['profile_image']);
        }
        if (!empty($target_user['id_front_image']) && file_exists($upload_dir . $target_user['id_front_image'])) {
            @unlink($upload_dir . $target_user['id_front_image']);
        }
        if (!empty($target_user['id_back_image']) && file_exists($upload_dir . $target_user['id_back_image'])) {
            @unlink($upload_dir . $target_user['id_back_image']);
        }

        $delete_stmt = $pdo->prepare("DELETE FROM `USER` WHERE `User_ID` = :id");
        $delete_stmt->execute([':id' => $target_user_id]);

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
        // SMTP Server Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fitcampus.physicaledu.uoc.fake@gmail.com';
        $mail->Password   = 'xrjmcrphjdsxombh'; // 16-digit App Password without spaces
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Bypass Localhost SSL Certificate Verification
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // Sender & Recipient Details
        $mail->setFrom('fitcampus.physicaledu.uoc.fake@gmail.com', 'FitCampus Physical Education');
        $mail->addAddress($recipient_email, $recipient_name);

        // Content
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