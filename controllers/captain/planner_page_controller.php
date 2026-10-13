<?php
// controllers/captain/planner_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Workout Planner | FitCampus";

$extra_js = [
    "captain/planner.js"
];

require_once '../../includes/db_connection.php';
require_once '../../models/captain/PlannerModel.php';

$plannerModel = new PlannerModel($pdo);
$user_id = (int)$_SESSION['user_id'];
$captain_teams = $plannerModel->getCaptainTeams($user_id);

if (empty($captain_teams)) {
    header("Location: ../member/dashboard.php");
    exit;
}

$workouts = $plannerModel->getTeamWorkoutsByUser($user_id);
$is_captain = true;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>