<?php
// controllers/member/workout_details_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$session_role = strtolower($_SESSION['role'] ?? '');
if (!in_array($session_role, ['member', 'student'], true)) {
    header('Location: ../auth/login.php');
    exit;
}

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
$workout_id = filter_input(INPUT_GET, 'workout_id', FILTER_VALIDATE_INT);
if (!$booking_id || $booking_id < 1 || !$workout_id || $workout_id < 1) {
    http_response_code(404);
    $workout_details = null;
} else {
    require_once '../../includes/db_connection.php';
    require_once '../../models/member/WorkoutModel.php';

    $workoutModel = new WorkoutModel($pdo);
    $workout_details = $workoutModel->getAssignedWorkoutDetailsForMember(
        $booking_id,
        $workout_id,
        (int)$_SESSION['user_id']
    );

    if ($workout_details === null) {
        http_response_code(404);
    }
}

$page_title = $workout_details
    ? $workout_details['Title'] . ' | Team Workout'
    : 'Workout unavailable | FitCampus';
?>