<?php
// controllers/member/goal_action.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connection.php';
require_once '../../models/member/GoalModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$goalModel = new GoalModel($pdo);
$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
$goalsUrl = '../../views/member/goals.php';

$redirectWithMessage = static function ($type, $message) use ($goalsUrl) {
    header('Location: ' . $goalsUrl . '?' . $type . '=' . urlencode($message));
    exit;
};

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !in_array($action, ['create_goal', 'update_progress', 'delete_goal'], true)
) {
    header('Location: ' . $goalsUrl);
    exit;
}

if (
    !is_string($_SESSION['csrf_token'] ?? null)
    || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $redirectWithMessage('error', 'Your session expired. Reload the page and try again.');
}

$parseDecimal = static function ($input, $allowZero = true) {
    if (
        !is_string($input)
        || !preg_match('/^\d+(?:\.\d{1,2})?$/D', $input)
    ) {
        return null;
    }

    $value = (float) $input;
    if (
        !is_finite($value)
        || $value > 99999999.99
        || (!$allowZero && $value <= 0)
    ) {
        return null;
    }
    return $value;
};

$parsePositiveInteger = static function ($input) {
    if (!is_string($input) || !preg_match('/^[1-9]\d{0,7}$/D', $input)) {
        return null;
    }
    return (int) $input;
};

$isValidDate = static function ($date) {
    if (!is_string($date)) {
        return false;
    }
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
};

$getText = static function ($key, $default = '') {
    return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : $default;
};

if ($action === 'create_goal') {
    $goalName = $getText('goal_name');
    $goalType = $getText('goal_type');
    $endDate = $getText('end_date');
    $today = date('Y-m-d');

    if (!in_array($goalType, ['Weight Loss', 'Strength', 'Endurance'], true)) {
        $redirectWithMessage('error', 'Select a valid goal category.');
    }

    $goalNameLength = preg_match_all('/./us', $goalName);
    if (
        $goalName === ''
        || $goalNameLength === false
        || $goalNameLength > 150
        || !$isValidDate($endDate)
        || $endDate < $today
    ) {
        $redirectWithMessage('error', 'Enter a goal name and a valid target date.');
    }

    $activityName = $goalType === 'Weight Loss'
        ? 'Body weight'
        : $getText('activity_name');
    $activityNameLength = preg_match_all('/./us', $activityName);
    if (
        $activityName === ''
        || $activityNameLength === false
        || $activityNameLength > 150
    ) {
        $redirectWithMessage('error', 'Select a valid category and enter an exercise or activity.');
    }

    $metrics = [];
    $startValue = 0.0;
    $currentValue = 0.0;
    $initialValues = [];

    switch ($goalType) {
        case 'Weight Loss':
            $startValue = $parseDecimal($_POST['start_value'] ?? null, false);
            $target = $parseDecimal($_POST['weight_target_weight'] ?? null, false);
            if ($startValue === null || $target === null || $target >= $startValue) {
                $redirectWithMessage('error', 'Target weight must be lower than a valid starting weight.');
            }
            $currentValue = $startValue;
            $metrics[] = [
                'key' => 'weight',
                'label' => 'Body weight',
                'unit' => 'kg',
                'target' => $target,
                'direction' => 'at_most',
                'baseline' => $startValue
            ];
            $initialValues['weight'] = $startValue;
            break;

        case 'Strength':
            $targetWeight = $parseDecimal($_POST['strength_target_weight'] ?? null, false);
            $targetRepetitions = $parsePositiveInteger($_POST['target_repetitions'] ?? null);
            if ($targetWeight === null || $targetRepetitions === null) {
                $redirectWithMessage('error', 'Enter a valid target weight and whole-number repetition count.');
            }
            $metrics[] = [
                'key' => 'weight',
                'label' => 'Weight',
                'unit' => 'kg',
                'target' => $targetWeight,
                'direction' => 'at_least',
                'baseline' => null
            ];
            $metrics[] = [
                'key' => 'repetitions',
                'label' => 'Repetitions',
                'unit' => 'reps',
                'target' => $targetRepetitions,
                'direction' => 'at_least',
                'baseline' => null
            ];
            break;

        case 'Endurance':
            $targetDistance = $parseDecimal($_POST['target_distance'] ?? null, false);
            $targetDuration = $parseDecimal($_POST['target_duration'] ?? null, false);
            if ($targetDistance === null || $targetDuration === null) {
                $redirectWithMessage('error', 'Enter a valid target distance and duration.');
            }
            $metrics[] = [
                'key' => 'distance',
                'label' => 'Distance',
                'unit' => 'km',
                'target' => $targetDistance,
                'direction' => 'at_least',
                'baseline' => null
            ];
            $metrics[] = [
                'key' => 'duration',
                'label' => 'Duration',
                'unit' => 'min',
                'target' => $targetDuration,
                'direction' => 'at_most',
                'baseline' => null
            ];
            break;
    }

    try {
        $created = $goalModel->createGoal($userId, [
            'goal_name' => $goalName,
            'activity_name' => $activityName,
            'goal_type' => $goalType,
            'start_value' => $startValue,
            'current_value' => $currentValue,
            'start_date' => $today,
            'end_date' => $endDate,
            'metrics' => $metrics,
            'initial_values' => $initialValues
        ]);
        $redirectWithMessage(
            $created ? 'success' : 'error',
            $created ? 'Goal created successfully.' : 'Unable to create goal.'
        );
    } catch (PDOException $e) {
        error_log('Personal goal creation failed: ' . $e->getMessage());
        $redirectWithMessage('error', 'Database error while creating goal.');
    }
}

if ($action === 'update_progress') {
    $goalIdInput = is_string($_POST['goal_id'] ?? null)
        ? filter_var($_POST['goal_id'], FILTER_VALIDATE_INT)
        : false;
    $goalId = $goalIdInput !== false ? (int) $goalIdInput : 0;
    $updateDate = $getText('update_date');
    $submittedMetrics = is_array($_POST['metrics'] ?? null) ? $_POST['metrics'] : null;

    if ($goalId <= 0 || !$isValidDate($updateDate) || $submittedMetrics === null) {
        $redirectWithMessage('error', 'Enter valid progress values and a date.');
    }

    $goal = $goalModel->getGoalByIdAndUser($goalId, $userId);
    if (!$goal) {
        $redirectWithMessage('error', 'Goal not found.');
    }

    $today = date('Y-m-d');
    if (
        (!empty($goal['Start_Date']) && $updateDate < $goal['Start_Date'])
        || $updateDate > $today
    ) {
        $redirectWithMessage('error', 'Progress date must be between the goal start date and today.');
    }

    $metrics = [];
    foreach ($goal['Metrics'] as $metric) {
        $key = $metric['Metric_Key'];
        if (!array_key_exists($key, $submittedMetrics)) {
            $redirectWithMessage('error', 'Enter every measurement required for this goal.');
        }
        if ($key === 'repetitions') {
            $value = $parsePositiveInteger($submittedMetrics[$key]);
        } else {
            $value = $parseDecimal($submittedMetrics[$key]);
        }
        if ($value === null) {
            $redirectWithMessage('error', 'Measurements must be valid numbers with up to two decimal places.');
        }
        if (in_array($key, ['distance', 'duration', 'weight'], true) && $value <= 0) {
            $redirectWithMessage('error', 'Weight, distance and duration must be greater than zero.');
        }
        $metrics[$key] = $value;
    }
    if (count($submittedMetrics) !== count($goal['Metrics'])) {
        $redirectWithMessage('error', 'The submitted measurements do not match this goal.');
    }

    try {
        $updated = $goalModel->updateGoalProgress(
            $goalId,
            $userId,
            $metrics,
            $updateDate
        );
        $redirectWithMessage(
            $updated ? 'success' : 'error',
            $updated ? 'Progress updated successfully.' : 'Unable to update progress.'
        );
    } catch (PDOException $e) {
        error_log('Personal goal progress update failed: ' . $e->getMessage());
        $redirectWithMessage('error', 'Database error while updating progress.');
    }
}

if ($action === 'delete_goal') {
    $goalIdInput = is_string($_POST['goal_id'] ?? null)
        ? filter_var($_POST['goal_id'], FILTER_VALIDATE_INT)
        : false;
    $goalId = $goalIdInput !== false ? (int) $goalIdInput : 0;
    if ($goalId <= 0 || !$goalModel->getGoalByIdAndUser($goalId, $userId)) {
        $redirectWithMessage('error', 'Goal not found.');
    }

    try {
        $deleted = $goalModel->deleteGoal($goalId, $userId);
        $redirectWithMessage(
            $deleted ? 'success' : 'error',
            $deleted ? 'Goal deleted successfully.' : 'Unable to delete goal.'
        );
    } catch (PDOException $e) {
        error_log('Personal goal deletion failed: ' . $e->getMessage());
        $redirectWithMessage('error', 'Database error while deleting goal.');
    }
}
