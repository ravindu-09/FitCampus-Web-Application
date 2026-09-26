<?php
// controllers/member/leaderboard_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Leaderboard | FitCampus";

$extra_js = [
    "member/leaderboard.js"
];

require_once '../../includes/db_connection.php';
require_once '../../models/member/LeaderboardModel.php';

$leaderboardModel = new LeaderboardModel($pdo);
$rankings = $leaderboardModel->getLeaderboardRankings();

// Check if the logged-in member is a captain based on session[cite: 15]
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>