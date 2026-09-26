<?php
// controllers/member/calories_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Nutrition & Activity Tracker | FitCampus";

$extra_js = [
    "member/calories.js"
];

// Check if the logged-in member is a captain based on session
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>