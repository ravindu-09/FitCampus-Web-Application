<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function kioskRespond(bool $success, string $message, int $statusCode = 200, array $data = []): void
{
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message] + $data);
    exit();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    kioskRespond(false, 'Invalid request method.', 405);
}
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    kioskRespond(false, 'Please sign in as an instructor.', 401);
}
if (!is_string($_SESSION['csrf_token'] ?? null)
    || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    kioskRespond(false, 'Your session expired. Reload the page and try again.', 403);
}

require_once '../../includes/db_connection.php';
require_once '../../models/instructor/KioskModel.php';

$model = new KioskModel($pdo);
$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
$registrationNumber = is_string($_POST['reg_no'] ?? null) ? trim($_POST['reg_no']) : '';
if ($registrationNumber === '' || mb_strlen($registrationNumber) > 50) {
    kioskRespond(false, 'Enter a valid registration number.', 422);
}

try {
    if ($action === 'manual_checkin') {
        $facilityId = filter_var($_POST['facility_id'] ?? null, FILTER_VALIDATE_INT);
        if ($facilityId === false || $facilityId < 1) {
            kioskRespond(false, 'Select a valid facility.', 422);
        }
        $visit = $model->recordManualCheckIn($registrationNumber, $facilityId);
        kioskRespond(true, 'Member checked in successfully.', 200, $visit);
    }

    if ($action === 'manual_checkout') {
        $visit = $model->recordManualCheckOut($registrationNumber);
        $message = $visit['points_awarded'] > 0
            ? 'Member checked out and earned ' . $visit['points_awarded'] . ' points.'
            : 'Member checked out. No additional visit points were awarded.';
        kioskRespond(true, $message, 200, $visit);
    }

    kioskRespond(false, 'Invalid attendance action.', 400);
} catch (InvalidArgumentException $e) {
    kioskRespond(false, $e->getMessage(), 422);
} catch (DomainException $e) {
    kioskRespond(false, $e->getMessage(), 409);
} catch (Throwable $e) {
    error_log('Instructor kiosk action failed: ' . $e->getMessage());
    kioskRespond(false, 'The attendance action could not be completed. Please try again.', 500);
}
