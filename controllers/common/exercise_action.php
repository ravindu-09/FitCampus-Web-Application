<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function exerciseRespond(bool $success, string $message, int $statusCode = 200, array $data = []): void
{
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message] + $data);
    exit();
}

$role = strtolower($_SESSION['role'] ?? '');
$allowedRoles = ['member', 'student', 'instructor'];
if (empty($_SESSION['user_id']) || !in_array($role, $allowedRoles, true)) {
    exerciseRespond(false, 'Please sign in to use the exercise catalogue.', 401);
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/WorkoutExerciseModel.php';

$exerciseModel = new WorkoutExerciseModel($pdo);
$method = $_SERVER['REQUEST_METHOD'] ?? '';

if ($method === 'GET') {
    try {
        $term = $_GET['term'] ?? '';
        if (!is_string($term) || mb_strlen($term, 'UTF-8') > 100) {
            exerciseRespond(false, 'Enter a search term of no more than 100 characters.', 422);
        }
        $results = $exerciseModel->searchExercises($term);
        exerciseRespond(true, 'Exercise search complete.', 200, $results);
    } catch (InvalidArgumentException $e) {
        exerciseRespond(false, $e->getMessage(), 422);
    } catch (Throwable $e) {
        error_log('Exercise search failed: ' . $e->getMessage());
        exerciseRespond(false, 'Exercise search is unavailable. Please try again.', 500);
    }
}

if ($method !== 'POST') {
    exerciseRespond(false, 'Invalid request method.', 405);
}
if (!is_string($_SESSION['csrf_token'] ?? null)
    || !is_string($_POST['csrf_token'] ?? null)
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    exerciseRespond(false, 'Your session expired. Reload the page and try again.', 403);
}
if (!is_string($_POST['name'] ?? null)) {
    exerciseRespond(false, 'Enter a valid exercise name.', 422);
}

try {
    $exercise = $exerciseModel->getOrCreateExercise($_POST['name']);
    exerciseRespond(true, $exercise['created'] ? 'Exercise added to the shared catalogue.' : 'Existing exercise selected.', 200, [
        'exercise_id' => $exercise['id'],
        'exercise_name' => $exercise['name'],
        'created' => $exercise['created'],
    ]);
} catch (InvalidArgumentException $e) {
    exerciseRespond(false, $e->getMessage(), 422);
} catch (Throwable $e) {
    error_log('Exercise creation failed: ' . $e->getMessage());
    exerciseRespond(false, 'The exercise could not be added. Please try again.', 500);
}
