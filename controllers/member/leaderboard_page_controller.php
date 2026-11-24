<?php
// controllers/member/leaderboard_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])
    || !in_array(strtolower((string)($_SESSION['role'] ?? '')), ['member', 'student'], true)) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$page_title = "Leaderboard | FitCampus";

$extra_js = [
    "member/leaderboard.js?v=" . filemtime(__DIR__ . '/../../assets/js/member/leaderboard.js')
];

require_once '../../includes/db_connection.php';
require_once '../../models/member/LeaderboardModel.php';

$leaderboardModel = new LeaderboardModel($pdo);
$period = is_string($_GET['period'] ?? null) ? $_GET['period'] : 'month';
if (!in_array($period, ['month', 'all', 'faculty'], true)) {
    $period = 'month';
}

$life_summary = $leaderboardModel->getMemberLifeSummary($user_id);
$faculty_options = $leaderboardModel->getFacultyOptions();
$selected_faculty = null;
if ($period === 'faculty') {
    $requested_faculty = is_string($_GET['faculty'] ?? null) ? $_GET['faculty'] : '';
    $member_faculty = (string)($life_summary['Faculty'] ?? '');
    if (in_array($requested_faculty, $faculty_options, true)) {
        $selected_faculty = $requested_faculty;
    } elseif (in_array($member_faculty, $faculty_options, true)) {
        $selected_faculty = $member_faculty;
    } else {
        $selected_faculty = $faculty_options[0] ?? '';
    }
}

$rankings = $leaderboardModel->getLeaderboardRankings($period, $selected_faculty);
$current_rank = null;
foreach ($rankings as $ranking) {
    if ($ranking['User_ID'] === $user_id) {
        $current_rank = $ranking;
        break;
    }
}
$podium_rankings = array_slice($rankings, 0, 3);
$member_penalties = $leaderboardModel->getMemberPenalties($user_id);

$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>