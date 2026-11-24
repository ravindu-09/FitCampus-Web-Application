<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function instructorWorkoutRespond(bool $success, string $message, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    instructorWorkoutRespond(false, 'Invalid request method.', 405);
}
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    instructorWorkoutRespond(false, 'Please sign in as an instructor.', 401);
}
if (!is_string($_SESSION['csrf_token'] ?? null)
    || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    instructorWorkoutRespond(false, 'Your session expired. Reload the page and try again.', 403);
}

require_once '../../includes/db_connection.php';
require_once '../../models/instructor/WorkoutModel.php';

$model = new InstructorWorkoutModel($pdo);
$instructorId = (int)$_SESSION['user_id'];
$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
$workoutId = is_scalar($_POST['workout_id'] ?? null)
    ? filter_var($_POST['workout_id'], FILTER_VALIDATE_INT)
    : false;

if (!in_array($action, ['create', 'update', 'delete'], true)) {
    instructorWorkoutRespond(false, 'Invalid workout action.', 400);
}
if (in_array($action, ['update', 'delete'], true)
    && ($workoutId === false || $workoutId < 1)) {
    instructorWorkoutRespond(false, 'Select a valid recommendation.', 422);
}

try {
    if ($action === 'delete') {
        if (!$model->deleteWorkout($workoutId, $instructorId)) {
            instructorWorkoutRespond(false, 'Recommendation not found.', 404);
        }
        instructorWorkoutRespond(true, 'Recommendation deleted.');
    }

    if (!is_string($_POST['title'] ?? null)
        || !is_string($_POST['description'] ?? null)
        || !is_string($_POST['level'] ?? null)
        || !is_scalar($_POST['duration'] ?? null)) {
        instructorWorkoutRespond(false, 'Enter valid workout details.', 422);
    }
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $duration = filter_var($_POST['duration'], FILTER_VALIDATE_INT);
    $level = trim($_POST['level']);
    if ($title === '' || mb_strlen($title) > 150) {
        instructorWorkoutRespond(false, 'Enter a workout title of 1 to 150 characters.', 422);
    }
    if ($description !== '' && mb_strlen($description) > 10000) {
        instructorWorkoutRespond(false, 'Instructions must be 10,000 characters or fewer.', 422);
    }
    if ($duration === false || $duration < 1 || $duration > 1440) {
        instructorWorkoutRespond(false, 'Enter a duration between 1 and 1,440 minutes.', 422);
    }
    if (!in_array($level, ['Beginner', 'Intermediate', 'Advanced'], true)) {
        instructorWorkoutRespond(false, 'Select a valid difficulty level.', 422);
    }

    $submittedExercises = [
        'id' => $_POST['exercise_id'] ?? null,
        'name' => $_POST['exercise_name'] ?? null,
        'sets' => $_POST['exercise_sets'] ?? null,
        'reps' => $_POST['exercise_reps'] ?? null,
    ];
    foreach ($submittedExercises as $values) {
        if (!is_array($values)) {
            instructorWorkoutRespond(false, 'Add complete exercise details.', 422);
        }
    }

    $routine = [
        'title' => $title,
        'description' => $description,
        'duration' => $duration,
        'level' => $level,
    ];
    if ($action === 'create') {
        $model->createWorkout($instructorId, $routine, $submittedExercises);
        instructorWorkoutRespond(true, 'Recommendation published and visible to members.');
    }

    $model->updateWorkout($workoutId, $instructorId, $routine, $submittedExercises);
    instructorWorkoutRespond(true, 'Recommendation updated.');
} catch (InvalidArgumentException $e) {
    instructorWorkoutRespond(false, $e->getMessage(), 422);
} catch (DomainException $e) {
    instructorWorkoutRespond(false, $e->getMessage(), 404);
} catch (Throwable $e) {
    error_log('Instructor workout action failed: ' . $e->getMessage());
    instructorWorkoutRespond(false, 'The recommendation could not be saved. Please try again.', 500);
}
