<?php
// controllers/member/update_avatar.php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/member/SettingsModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: ../../views/member/settings.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? 'upload';
$upload_dir = '../../assets/images/uploads/';
$settingsModel = new SettingsModel($pdo);

if ($action === 'remove') {
    try {
        $current_img = $settingsModel->getProfileImage($user_id);

        if (!empty($current_img) && $current_img !== 'default_avatar.png') {
            $existing_file = $upload_dir . $current_img;
            if (file_exists($existing_file)) {
                @unlink($existing_file);
            }
        }

        $settingsModel->updateProfileImage($user_id, 'default_avatar.png');

        $_SESSION['profile_image'] = 'default_avatar.png';
        $_SESSION['settings_success'] = "Profile photo removed successfully.";
    } catch (\PDOException $e) {
        error_log("Remove Avatar Error: " . $e->getMessage());
        $_SESSION['settings_error'] = "Failed to remove profile photo.";
    }

    header("Location: ../../views/member/settings.php");
    exit();
}

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file_tmp  = $_FILES['profile_image']['tmp_name'];
    $file_size = $_FILES['profile_image']['size'];
    $file_name = $_FILES['profile_image']['name'];

    if ($file_size > 2 * 1024 * 1024) {
        $_SESSION['settings_error'] = "File size must be under 2MB.";
        header("Location: ../../views/member/settings.php");
        exit();
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        $_SESSION['settings_error'] = "Invalid file type. Only JPG, PNG, and WEBP allowed.";
        header("Location: ../../views/member/settings.php");
        exit();
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $old_img = $settingsModel->getProfileImage($user_id);
    if (!empty($old_img) && $old_img !== 'default_avatar.png' && file_exists($upload_dir . $old_img)) {
        @unlink($upload_dir . $old_img);
    }

    $new_file_name = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
    $destination = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $destination)) {
        $settingsModel->updateProfileImage($user_id, $new_file_name);

        $_SESSION['profile_image'] = $new_file_name;
        $_SESSION['settings_success'] = "Profile picture updated successfully.";
    } else {
        $_SESSION['settings_error'] = "Failed to upload image. Please try again.";
    }
} else {
    $_SESSION['settings_error'] = "No valid image file was selected.";
}

header("Location: ../../views/member/settings.php");
exit();