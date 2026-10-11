<?php
// controllers/common/support_request_action.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: ../../views/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/common/support.php');
    exit();
}

if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['support_error'] = 'Your session expired. Please try submitting the request again.';
    header('Location: ../../views/common/support.php');
    exit();
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/SupportRequestModel.php';

$userId = (int)$_SESSION['user_id'];
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$requestedEndDate = null;
$attachmentPath = null;
$allowedCategories = ['account_extension', 'life_bar_replenishment', 'complaint_feedback'];

if (!in_array($category, $allowedCategories, true) || $description === '' || mb_strlen($description) > 5000) {
    $_SESSION['support_error'] = 'Choose a request type and provide a description of no more than 5,000 characters.';
    header('Location: ../../views/common/support.php');
    exit();
}

$isExtension = $category === 'account_extension';
if ($isExtension) {
    $profileModel = new SupportRequestModel($pdo);
    $profile = $profileModel->getUserProfile($userId);
    $requestedEndDate = trim($_POST['requested_end_date'] ?? '');
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $requestedEndDate);
    $dateErrors = DateTimeImmutable::getLastErrors();

    if (!$profile || $profile['Role'] !== 'Student') {
        $_SESSION['support_error'] = 'Account extension requests are available to student accounts only.';
        header('Location: ../../views/common/support.php');
        exit();
    }
    if (!$date || ($dateErrors !== false && ($dateErrors['warning_count'] || $dateErrors['error_count'])) || $date->format('Y-m-d') !== $requestedEndDate || $date <= new DateTimeImmutable('today')) {
        $_SESSION['support_error'] = 'Select a valid future date for the requested account extension.';
        header('Location: ../../views/common/support.php');
        exit();
    }
} else {
    $requestedEndDate = null;
}

$file = $_FILES['attachment'] ?? null;
$hasUpload = $file && $file['error'] !== UPLOAD_ERR_NO_FILE;
if ($isExtension && !$hasUpload) {
    $_SESSION['support_error'] = 'A photo of your new student ID is required for an account extension request.';
    header('Location: ../../views/common/support.php');
    exit();
}

if ($hasUpload) {
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
        $_SESSION['support_error'] = 'The image could not be uploaded. Choose an image under 5 MB and try again.';
        header('Location: ../../views/common/support.php');
        exit();
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    if (!isset($extensions[$mimeType])) {
        $_SESSION['support_error'] = 'Upload a JPEG, PNG, or WEBP image.';
        header('Location: ../../views/common/support.php');
        exit();
    }

    $storageDirectory = dirname(__DIR__, 2) . '/storage/support_requests';
    if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0750, true) && !is_dir($storageDirectory)) {
        error_log('Unable to create private support attachment directory.');
        $_SESSION['support_error'] = 'The attachment could not be stored. Please try again later.';
        header('Location: ../../views/common/support.php');
        exit();
    }

    $attachmentPath = bin2hex(random_bytes(24)) . '.' . $extensions[$mimeType];
    if (!move_uploaded_file($file['tmp_name'], $storageDirectory . '/' . $attachmentPath)) {
        $_SESSION['support_error'] = 'The attachment could not be stored. Please try again.';
        header('Location: ../../views/common/support.php');
        exit();
    }
}

try {
    $supportModel = new SupportRequestModel($pdo);
    $supportModel->createRequest($userId, $category, $description, $attachmentPath, $requestedEndDate);
    $_SESSION['support_success'] = 'Your request has been sent to the FitCampus administrators.';
} catch (Throwable $e) {
    if ($attachmentPath !== null) {
        @unlink(dirname(__DIR__, 2) . '/storage/support_requests/' . $attachmentPath);
    }
    error_log('Support request submission error: ' . $e->getMessage());
    $_SESSION['support_error'] = 'Your request could not be sent. Please try again later.';
}

header('Location: ../../views/common/support.php');
exit();
?>