<?php
// bll/admin/VerificationBLL.php
require_once '../../dal/admin/VerificationDAL.php';

// Load PHPMailer files directly
require_once '../../includes/PHPMailer/Exception.php';
require_once '../../includes/PHPMailer/SMTP.php';
require_once '../../includes/PHPMailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class VerificationBLL {
    private $dal;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->dal = new VerificationDAL($pdo);
    }

    // Get statistics and pending lists for view rendering
    public function getVerificationData() {
        try {
            $pending_users = $this->dal->getPendingApplicants();
            $count_pending = count($pending_users);
            $count_approved = $this->dal->getApprovedCount();
            $count_total_members = $this->dal->getTotalMembersCount();

            return [
                'pending_users' => $pending_users,
                'count_pending' => $count_pending,
                'count_approved' => $count_approved,
                'count_total_members' => $count_total_members
            ];
        } catch (\PDOException $e) {
            error_log("Verification Fetch Error: " . $e->getMessage());
            return [
                'pending_users' => [],
                'count_pending' => 0,
                'count_approved' => 0,
                'count_total_members' => 0
            ];
        }
    }

    // Process applicant verification action (Approve or Reject)
    public function processVerification($post_data) {
        $target_user_id = (int)($post_data['user_id'] ?? 0);
        $action = trim($post_data['action'] ?? '');
        $user_provided_reason = trim($post_data['rejection_reason'] ?? '');
        $default_reason = 'Provided verification documents or academic details did not meet the required criteria.';
        $rejection_note = !empty($user_provided_reason) ? $user_provided_reason : $default_reason;

        if ($target_user_id <= 0 || !in_array($action, ['approve', 'reject'])) {
            throw new Exception("Invalid request parameters.");
        }

        $target_user = $this->dal->getApplicantById($target_user_id);
        if (!$target_user) {
            throw new Exception("Registration request not found or already processed.");
        }

        $user_name = $target_user['full_name'];
        $user_email = $target_user['Email'];

        // ACTION 1: APPROVE USER
        if ($action === 'approve') {
            $this->dal->updateStudentStatusActive($target_user_id);

            $subject = "FitCampus - Registration Approved!";
            $message = "Dear {$user_name},\n\n" .
                       "Your registration request for the FitCampus Institutional Portal has been verified and APPROVED.\n" .
                       "You can now access your member dashboard, reserve workout slots, and view real-time facility updates.\n\n" .
                       "Sign In Portal: http://localhost/FitCampus-Web-Application/views/auth/login.php\n\n" .
                       "Best Regards,\n" .
                       "Department of Physical Education\n" .
                       "University of Colombo";

            $this->sendFitCampusEmail($user_email, $user_name, $subject, $message);
            return "Member '{$user_name}' approved successfully and notification email sent.";

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

            $this->dal->deleteRejectedUser($target_user_id);

            $subject = "FitCampus - Registration Request Update";
            $message = "Dear {$user_name},\n\n" .
                       "We regret to inform you that your registration request for FitCampus could not be approved at this time.\n\n" .
                       "Reason for Rejection:\n" .
                       "{$rejection_note}\n\n" .
                       "If you believe this was an error, please re-register with valid documents or visit the Physical Education Department desk.\n\n" .
                       "Best Regards,\n" .
                       "Department of Physical Education\n" .
                       "University of Colombo";

            $this->sendFitCampusEmail($user_email, $user_name, $subject, $message);
            return "Member registration declined, applicant completely removed from database, and notification email sent.";
        }
    }

    /**
     * SMTP Mail Dispatcher via PHPMailer
     */
    private function sendFitCampusEmail($recipient_email, $recipient_name, $subject, $plain_body) {
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
}