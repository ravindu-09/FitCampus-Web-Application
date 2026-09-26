<?php
// controllers/admin/verification_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connection.php';
require_once '../../models/admin/VerificationModel.php';

$verificationModel = new VerificationModel($pdo);

try {
    $pending_users = $verificationModel->getPendingUsers();
    
    $count_pending = count($pending_users);
    $count_approved = $verificationModel->getApprovedCount();
    $count_total_members = $verificationModel->getTotalMembersCount();

} catch (\PDOException $e) {
    error_log("Verification Fetch Error: " . $e->getMessage());
    $pending_users = [];
    $count_pending = 0;
    $count_approved = 0;
    $count_total_members = 0;
}
?>