<?php
// controllers/member/settings_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Account Settings | FitCampus";

$extra_js = [
    "member/settings.js"
];

require_once '../../includes/db_connection.php';
require_once '../../models/member/SettingsModel.php';

$settingsModel = new SettingsModel($pdo);
$member = $settingsModel->getMemberDetails($user_id);

if (!$member) {
    header("Location: ../../backend/auth/logout.php");
    exit;
}

$avatar_filename = $member['Profile_Image'] ?? 'default_avatar.png';
$has_custom_avatar = !empty($avatar_filename) && $avatar_filename !== 'default_avatar.png' && file_exists('../../assets/images/uploads/' . $avatar_filename);
$avatar_path = $has_custom_avatar ? '../../assets/images/uploads/' . htmlspecialchars($avatar_filename) : null;

$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>