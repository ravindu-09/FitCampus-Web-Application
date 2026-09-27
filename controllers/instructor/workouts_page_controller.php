<?php
// controllers/instructor/workouts_page_controller.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instructor authorization guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    $_SESSION['error'] = "Unauthorized access. Please login as an instructor.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$page_title = "Common Workouts Manager - FitCampus";
$extra_js = ["instructor/workouts.js"];

require_once '../../includes/db_connection.php';
// require_once '../../models/instructor/WorkoutModel.php';
// $workoutModel = new WorkoutModel($pdo);
// $workouts_list = $workoutModel->getCommonWorkouts();
?>