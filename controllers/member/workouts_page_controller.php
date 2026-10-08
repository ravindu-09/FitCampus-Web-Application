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
    "member/workouts.js"
];

// Check if the logged-in member is a captain based on session[cite: 19]
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>