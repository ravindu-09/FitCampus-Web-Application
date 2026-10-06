<?php
// controllers/common/support_attachment.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Authentication required.');
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/SupportRequestModel.php';

$requestId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$requestId) {
    http_response_code(404);
    exit('Attachment not found.');
}

$supportModel = new SupportRequestModel($pdo);
$request = $supportModel->getRequestAttachment($requestId);
$isAdmin = strtolower($_SESSION['role'] ?? '') === 'admin';
if (!$request || (!$isAdmin && (int)$request['User_ID'] !== (int)$_SESSION['user_id'])) {
    http_response_code(404);
    exit('Attachment not found.');
}

$filename = basename($request['Attachment_Path'] ?? '');
if ($filename === '' || !preg_match('/\A[a-f0-9]{48}\.(jpg|png|webp)\z/', $filename)) {
    http_response_code(404);
    exit('Attachment not found.');
}

$filePath = dirname(__DIR__, 2) . '/storage/support_requests/' . $filename;
if (!is_file($filePath)) {
    http_response_code(404);
    exit('Attachment not found.');
}

$mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($filePath);
if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(415);
    exit('Unsupported attachment type.');
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="support-attachment.' . pathinfo($filename, PATHINFO_EXTENSION) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($filePath);
?>