<?php
// controllers/member/teams_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Team Hub | FitCampus";

$extra_js = [
    "member/teams.js"
];

require_once '../../includes/db_connection.php';
require_once '../../models/member/TeamModel.php';

$teamModel = new TeamModel($pdo);
$teams = $teamModel->getTeamsByUser($user_id);

// Check if the logged-in member is a captain based on session[cite: 18]
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>