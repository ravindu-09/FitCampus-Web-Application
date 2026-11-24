<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

$teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
$redirectUrl = '../../views/captain/team_goals.php';
if ($teamId && $teamId > 0) {
    $redirectUrl .= '?team_id=' . (int)$teamId;
}
$querySeparator = strpos($redirectUrl, '?') === false ? '?' : '&';

if (empty($_SESSION['user_id'])) {
    header('Location: ../../views/auth/login.php');
    exit;
}
if (empty($_SESSION['csrf_token']) || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: ' . $redirectUrl . $querySeparator . 'error=' . urlencode('Your session expired. Reload the page and try again.'));
    exit;
}
if (!$teamId || $teamId < 1) {
    header('Location: ../../views/captain/team_goals.php?error=' . urlencode('Select a valid team.'));
    exit;
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/TeamGoalModel.php';

$model = new TeamGoalModel($pdo);
$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$goalId = filter_input(INPUT_POST, 'team_goal_id', FILTER_VALIDATE_INT);

try {
    if ($action === 'create' || $action === 'edit') {
        $readText = static function ($key) {
            return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
        };
        $name = $readText('goal_name');
        $description = $readText('description');
        $unit = $readText('unit');
        $targetInput = $_POST['target_value'] ?? '';
        $target = is_string($targetInput) ? filter_var($targetInput, FILTER_VALIDATE_FLOAT) : false;
        $startDate = $readText('start_date');
        $endDate = $readText('end_date');
        $validDate = static function ($date) {
            $parsed = DateTime::createFromFormat('!Y-m-d', $date);
            return $parsed && $parsed->format('Y-m-d') === $date;
        };

        if ($name === '' || strlen($name) > 150 || strlen($description) > 5000
            || $unit === '' || strlen($unit) > 50 || $target === false
            || !is_string($targetInput) || !preg_match('/^\d+(?:\.\d{1,2})?$/', $targetInput)
            || $target <= 0 || $target > 9999999999.99
            || !$validDate($startDate) || !$validDate($endDate)
            || $endDate < $startDate) {
            throw new InvalidArgumentException('Enter a name, positive target, unit, and valid goal dates.');
        }
        if ($action === 'create' && $startDate < date('Y-m-d')) {
            throw new InvalidArgumentException('A new team goal cannot start in the past.');
        }

        $goal = [
            'name' => $name,
            'description' => $description,
            'target' => $target,
            'unit' => $unit,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($action === 'create') {
            $model->createGoal($teamId, $userId, $goal);
            $message = 'Team goal created.';
        } else {
            if (!$goalId || $goalId < 1) {
                throw new InvalidArgumentException('Select a valid team goal.');
            }
            $model->updateGoal($goalId, $teamId, $userId, $goal);
            $message = 'Team goal updated.';
        }

        header('Location: ' . $redirectUrl . $querySeparator . 'success=' . urlencode($message));
        exit;
    }

    if ($action === 'status') {
        $status = $_POST['status'] ?? '';
        if (!$goalId || $goalId < 1) {
            throw new InvalidArgumentException('Select a valid team goal.');
        }
        $model->changeGoalStatus($goalId, $teamId, $userId, $status);
        header('Location: ' . $redirectUrl . $querySeparator . 'success=' . urlencode('Team goal status updated.'));
        exit;
    }

    throw new InvalidArgumentException('Choose a valid team goal action.');
} catch (InvalidArgumentException | DomainException $e) {
    header('Location: ' . $redirectUrl . $querySeparator . 'error=' . urlencode($e->getMessage()));
    exit;
} catch (Throwable $e) {
    error_log('Team goal action failed: ' . $e->getMessage());
    header('Location: ' . $redirectUrl . $querySeparator . 'error=' . urlencode('The team goal could not be saved. Please try again.'));
    exit;
}
