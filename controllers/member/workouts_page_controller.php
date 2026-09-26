<?php
// controllers/member/workouts_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Workout Builder | FitCampus";

$extra_js = [
    "member/workouts.js"
];

require_once '../../includes/db_connection.php';
require_once '../../models/member/WorkoutModel.php';

$workoutModel = new WorkoutModel($pdo);
$workouts = $workoutModel->getWorkoutsByUser($user_id);

// Check if the logged-in member is a captain based on session[cite: 19]
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>