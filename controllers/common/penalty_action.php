<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function penaltyRespond(bool $success, string $message, int $statusCode = 200, array $data = []): void
{
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message] + $data);
    exit();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    penaltyRespond(false, 'Invalid request method.', 405);
}

$issuerType = strtolower((string)($_SESSION['role'] ?? ''));
if (empty($_SESSION['user_id']) || !in_array($issuerType, ['admin', 'instructor'], true)) {
    penaltyRespond(false, 'Only administrators and instructors may issue penalties.', 401);
}
if (!is_string($_SESSION['csrf_token'] ?? null)
    || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    penaltyRespond(false, 'Your session expired. Reload the page and try again.', 403);
}

$userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
$requestedDeduction = filter_var($_POST['requested_life_deduction'] ?? null, FILTER_VALIDATE_INT);
$reason = is_string($_POST['reason'] ?? null) ? trim($_POST['reason']) : '';
$severity = is_string($_POST['severity'] ?? null) ? trim($_POST['severity']) : '';
$requestKey = is_string($_POST['request_key'] ?? null) ? $_POST['request_key'] : '';
if ($userId === false || $userId < 1
    || $requestedDeduction === false || $requestedDeduction < 1 || $requestedDeduction > 100) {
    penaltyRespond(false, 'Select a member and a requested deduction from 1 to 100 Life points.', 422);
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/LifePenaltyModel.php';

try {
    $model = new LifePenaltyModel($pdo);
    $result = $model->applyPenalty(
        $userId,
        (int)$_SESSION['user_id'],
        $issuerType,
        $reason,
        $severity,
        $requestedDeduction,
        $requestKey
    );
    $message = $result['replayed']
        ? 'This penalty was already recorded.'
        : 'Penalty recorded. Actual Life Bar loss: ' . $result['life_deduction']
            . ' of ' . $result['requested_life_deduction'] . ' requested.';
    penaltyRespond(
        true,
        $message,
        200,
        $result
    );
} catch (InvalidArgumentException $e) {
    penaltyRespond(false, $e->getMessage(), 422);
} catch (DomainException $e) {
    penaltyRespond(false, $e->getMessage(), 409);
} catch (Throwable $e) {
    error_log('Life penalty action failed: ' . $e->getMessage());
    penaltyRespond(false, 'The penalty could not be recorded. Please try again.', 500);
}
