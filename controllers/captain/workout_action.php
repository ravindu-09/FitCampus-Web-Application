<?php
// controllers/captain/workout_action.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function respond($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

if (empty($_SESSION['user_id'])) {
    respond(false, 'Please sign in again.', 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    respond(false, 'Your session expired. Reload the planner and try again.', 403);
}

require_once '../../includes/db_connection.php';
require_once '../../models/captain/PlannerModel.php';
require_once '../../models/captain/RosterModel.php';

$userId = (int)$_SESSION['user_id'];
$teamId = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
$workoutId = filter_input(INPUT_POST, 'workout_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$teamId || $teamId < 1) {
    respond(false, 'Select a valid team.', 422);
}

$rosterModel = new RosterModel($pdo);
if (!$rosterModel->isCaptain($teamId, $userId)) {
    respond(false, 'You are not a captain of the selected team.', 403);
}

$plannerModel = new PlannerModel($pdo);

if ($action === 'delete') {
    if (!$workoutId || $workoutId < 1) {
        respond(false, 'Select a valid workout.', 422);
    }

    try {
        $plannerModel->deleteWorkout($workoutId, $teamId, $userId);
        respond(true, 'Workout deleted.');
    } catch (DomainException $e) {
        respond(false, $e->getMessage(), 403);
    } catch (Throwable $e) {
        error_log('Team workout delete failed: ' . $e->getMessage());
        respond(false, 'The workout could not be deleted. Please try again.', 500);
    }
}

if (!in_array($action, ['create', 'edit'], true)) {
    respond(false, 'Invalid workout action.', 400);
}
if ($action === 'edit' && (!$workoutId || $workoutId < 1)) {
    respond(false, 'Select a valid workout to edit.', 422);
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration = filter_var($_POST['duration'] ?? null, FILTER_VALIDATE_INT);
$exerciseNames = $_POST['exercise_name'] ?? [];
$exerciseSets = $_POST['exercise_sets'] ?? [];
$exerciseReps = $_POST['exercise_reps'] ?? [];

if ($title === '' || mb_strlen($title) > 150) {
    respond(false, 'Enter a workout title of 1 to 150 characters.', 422);
}
if (mb_strlen($description) > 5000) {
    respond(false, 'The description must be 5,000 characters or fewer.', 422);
}
if ($duration === false || $duration < 1 || $duration > 1440) {
    respond(false, 'Enter a workout duration between 1 and 1,440 minutes.', 422);
}
if (!is_array($exerciseNames) || !is_array($exerciseSets) || !is_array($exerciseReps)
    || count($exerciseNames) < 1 || count($exerciseNames) > 40
    || count($exerciseNames) !== count($exerciseSets) || count($exerciseNames) !== count($exerciseReps)) {
    respond(false, 'Add between 1 and 40 complete exercises.', 422);
}

$exercises = [];
foreach ($exerciseNames as $index => $exerciseName) {
    $name = trim((string)$exerciseName);
    $sets = filter_var($exerciseSets[$index] ?? null, FILTER_VALIDATE_INT);
    $reps = filter_var($exerciseReps[$index] ?? null, FILTER_VALIDATE_INT);
    if ($name === '' || mb_strlen($name) > 100 || $sets === false || $sets < 1 || $sets > 1000
        || $reps === false || $reps < 1 || $reps > 1000) {
        respond(false, 'Each exercise needs a name, sets, and reps using valid positive numbers.', 422);
    }
    $exercises[] = ['name' => $name, 'sets' => $sets, 'reps' => $reps];
}

try {
    if ($action === 'create') {
        $plannerModel->createWorkout($teamId, $userId, $title, $description, $duration, $exercises);
        respond(true, 'Reusable team workout created.');
    }

    $plannerModel->updateWorkout($workoutId, $teamId, $userId, $title, $description, $duration, $exercises);
    respond(true, 'Reusable team workout updated.');
} catch (DomainException $e) {
    respond(false, $e->getMessage(), 403);
} catch (InvalidArgumentException $e) {
    respond(false, $e->getMessage(), 422);
} catch (Throwable $e) {
    error_log('Team workout save failed: ' . $e->getMessage());
    respond(false, 'The workout could not be saved. Please try again.', 500);
}
?>