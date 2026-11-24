<?php
// controllers/member/workouts_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$session_role = strtolower($_SESSION['role'] ?? '');
if (!in_array($session_role, ['member', 'student'], true)) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Workout Builder | FitCampus";

$extra_js = [
    "common/exercise-autocomplete.js",
    "member/workouts.js?v=" . filemtime(__DIR__ . '/../../assets/js/member/workouts.js')
];

require_once '../../includes/db_connection.php';
require_once '../../models/member/WorkoutModel.php';

$workoutModel = new WorkoutModel($pdo);
$user_id = (int)$_SESSION['user_id'];
$personal_workouts = $workoutModel->getPersonalWorkouts($user_id);
$recommendations = $workoutModel->getRecommendations($user_id);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Check if the logged-in member is a captain based on session[cite: 19]
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>