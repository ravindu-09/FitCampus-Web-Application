<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function workoutRespond(bool $success, string $message, int $statusCode = 200, array $data = []): void
{
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message] + $data);
    exit();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    workoutRespond(false, 'Invalid request method.', 405);
}
if (empty($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role'] ?? ''), ['member', 'student'], true)) {
    workoutRespond(false, 'Please sign in as a member.', 401);
}
if (!is_string($_SESSION['csrf_token'] ?? null)
    || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    workoutRespond(false, 'Your session expired. Reload the page and try again.', 403);
}

require_once '../../includes/db_connection.php';
require_once '../../models/member/WorkoutModel.php';

$model = new WorkoutModel($pdo);
$userId = (int)$_SESSION['user_id'];
$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
$workoutId = is_scalar($_POST['workout_id'] ?? null)
    ? filter_var($_POST['workout_id'], FILTER_VALIDATE_INT)
    : false;

if (!in_array($action, ['create', 'update', 'delete', 'save', 'unsave', 'copy'], true)) {
    workoutRespond(false, 'Invalid workout action.', 400);
}
if (in_array($action, ['update', 'delete', 'save', 'unsave', 'copy'], true)
    && ($workoutId === false || $workoutId < 1)) {
    workoutRespond(false, 'Select a valid workout.', 422);
}

try {
    if ($action === 'delete') {
        if (!$model->deletePersonalWorkout($workoutId, $userId)) {
            workoutRespond(false, 'Workout not found.', 404);
        }
        workoutRespond(true, 'Personal workout deleted.');
    }

    if ($action === 'save' || $action === 'unsave') {
        if ($action === 'save') {
            $model->saveRecommendation($userId, $workoutId);
            workoutRespond(true, 'Recommendation saved.');
        }
        $model->unsaveRecommendation($userId, $workoutId);
        workoutRespond(true, 'Recommendation removed from saved workouts.');
    }

    if ($action === 'copy') {
        $copy = $model->copyRecommendation($userId, $workoutId);
        workoutRespond(
            true,
            $copy['created'] ? 'Recommendation copied to your personal workouts.' : 'An identical personal copy already exists.',
            200,
            ['workout_id' => $copy['id'], 'created' => $copy['created']]
        );
    }

    if (!is_string($_POST['title'] ?? null) || !is_scalar($_POST['duration'] ?? null)) {
        workoutRespond(false, 'Enter a valid workout title and duration.', 422);
    }
    $title = trim($_POST['title']);
    $duration = filter_var($_POST['duration'], FILTER_VALIDATE_INT);
    if ($title === '' || mb_strlen($title) > 150) {
        workoutRespond(false, 'Enter a workout title of 1 to 150 characters.', 422);
    }
    if ($duration === false || $duration < 1 || $duration > 1440) {
        workoutRespond(false, 'Enter a duration between 1 and 1,440 minutes.', 422);
    }

    $submittedExercises = [
        'id' => $_POST['exercise_id'] ?? null,
        'name' => $_POST['exercise_name'] ?? null,
        'sets' => $_POST['exercise_sets'] ?? null,
        'reps' => $_POST['exercise_reps'] ?? null,
    ];
    foreach ($submittedExercises as $values) {
        if (!is_array($values)) {
            workoutRespond(false, 'Add complete exercise details.', 422);
        }
    }

    $routine = ['title' => $title, 'duration' => $duration];
    if ($action === 'create') {
        $model->createPersonalWorkout($userId, $routine, $submittedExercises);
        workoutRespond(true, 'Personal workout created.');
    }

    $model->updatePersonalWorkout($workoutId, $userId, $routine, $submittedExercises);
    workoutRespond(true, 'Personal workout updated.');
} catch (InvalidArgumentException $e) {
    workoutRespond(false, $e->getMessage(), 422);
} catch (DomainException $e) {
    workoutRespond(false, $e->getMessage(), 404);
} catch (Throwable $e) {
    error_log('Member workout action failed: ' . $e->getMessage());
    workoutRespond(false, 'The workout could not be saved. Please try again.', 500);
}
