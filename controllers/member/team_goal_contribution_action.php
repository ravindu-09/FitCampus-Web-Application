<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

$teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
$redirectUrl = '../../views/member/teams.php';
if ($teamId && $teamId > 0) {
    $redirectUrl .= '?team_id=' . (int)$teamId;
}
$querySeparator = strpos($redirectUrl, '?') === false ? '?' : '&';

if (empty($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role'] ?? ''), ['member', 'student'], true)) {
    header('Location: ../../views/auth/login.php');
    exit;
}
if (empty($_SESSION['csrf_token']) || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: ' . $redirectUrl . $querySeparator . 'error=' . urlencode('Your session expired. Reload the page and try again.'));
    exit;
}

$goalId = filter_input(INPUT_POST, 'team_goal_id', FILTER_VALIDATE_INT);
$date = is_string($_POST['progress_date'] ?? null) ? trim($_POST['progress_date']) : '';
$valueInput = $_POST['contribution_value'] ?? '';
$value = is_string($valueInput) ? filter_var($valueInput, FILTER_VALIDATE_FLOAT) : false;
$parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
if (!$teamId || $teamId < 1 || !$goalId || $goalId < 1
    || $value === false || !is_string($valueInput)
    || !preg_match('/^\d+(?:\.\d{1,2})?$/', $valueInput)
    || $value <= 0 || $value > 9999999999.99
    || !$parsedDate || $parsedDate->format('Y-m-d') !== $date
    || $date > date('Y-m-d')) {
    header('Location: ' . $redirectUrl . $querySeparator . 'error=' . urlencode('Enter a positive contribution and a valid date no later than today.'));
    exit;
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/TeamGoalModel.php';

try {
    $model = new TeamGoalModel($pdo);
    $model->addContribution($goalId, $teamId, (int)$_SESSION['user_id'], $value, $date);
    header('Location: ' . $redirectUrl . $querySeparator . 'success=' . urlencode('Your contribution was recorded.'));
    exit;
} catch (InvalidArgumentException | DomainException $e) {
    header('Location: ' . $redirectUrl . $querySeparator . 'error=' . urlencode($e->getMessage()));
    exit;
} catch (Throwable $e) {
    error_log('Team goal contribution failed: ' . $e->getMessage());
    header('Location: ' . $redirectUrl . $querySeparator . 'error=' . urlencode('Your contribution could not be recorded. Please try again.'));
    exit;
}
