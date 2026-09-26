<?php
// backend/member/update_avatar.php
session_start();
require_once '../../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: ../../views/member/settings.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? 'upload';
$upload_dir = '../../assets/images/uploads/';

/* 
   ACTION 1: REMOVE AVATAR & RESET TO DEFAULT
*/
if ($action === 'remove') {
    try {
        // Fetch current photo
        $stmt = $pdo->prepare("SELECT Profile_Image FROM `university_student` WHERE User_ID = :uid LIMIT 1");
        $stmt->execute([':uid' => $user_id]);
        $current = $stmt->fetch();

        if ($current && !empty($current['Profile_Image']) && $current['Profile_Image'] !== 'default_avatar.png') {
            $existing_file = $upload_dir . $current['Profile_Image'];
            if (file_exists($existing_file)) {
                @unlink($existing_file);
            }
        }

        // Reset to default_avatar.png
        $update_stmt = $pdo->prepare("UPDATE `university_student` SET Profile_Image = 'default_avatar.png' WHERE User_ID = :uid");
        $update_stmt->execute([':uid' => $user_id]);

        $_SESSION['profile_image'] = 'default_avatar.png';
        $_SESSION['settings_success'] = "Profile photo removed successfully.";
    } catch (\PDOException $e) {
        error_log("Remove Avatar Error: " . $e->getMessage());
        $_SESSION['settings_error'] = "Failed to remove profile photo.";
    }

    header("Location: ../../views/member/settings.php");
    exit();
}

/* 
   ACTION 2: UPLOAD NEW AVATAR
 */
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file_tmp  = $_FILES['profile_image']['tmp_name'];
    $file_size = $_FILES['profile_image']['size'];
    $file_name = $_FILES['profile_image']['name'];

    // Limit size to 2MB
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

    // Delete old custom image before uploading new
    $stmt = $pdo->prepare("SELECT Profile_Image FROM `university_student` WHERE User_ID = :uid LIMIT 1");
    $stmt->execute([':uid' => $user_id]);
    $old_img = $stmt->fetchColumn();

    if (!empty($old_img) && $old_img !== 'default_avatar.png' && file_exists($upload_dir . $old_img)) {
        @unlink($upload_dir . $old_img);
    }

    $new_file_name = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
    $destination = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $destination)) {
        $update_stmt = $pdo->prepare("UPDATE `university_student` SET Profile_Image = :img WHERE User_ID = :uid");
        $update_stmt->execute([
            ':img' => $new_file_name,
            ':uid' => $user_id
        ]);

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