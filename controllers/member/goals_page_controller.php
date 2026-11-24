<?php
// controllers/member/goals_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
if ($user_id <= 0) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}
$page_title = "Personal Fitness Goals | FitCampus";

$extra_js = [
    "member/goals.js?v=" . filemtime(__DIR__ . '/../../assets/js/member/goals.js')
];

require_once '../../includes/db_connection.php';
require_once '../../models/member/GoalModel.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$goalModel = new GoalModel($pdo);
$goals = $goalModel->getGoalsByUser($user_id);

// Check if the logged-in member is a captain based on session
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>