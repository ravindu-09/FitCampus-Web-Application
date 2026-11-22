<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionRole = strtolower($_SESSION['role'] ?? '');
if ( empty($_SESSION['user_id']) || $sessionRole !== 'member' || empty($_SESSION['is_captain']) ) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../includes/db_connection.php';
require_once '../../models/common/TeamGoalModel.php';

$userId = (int)$_SESSION['user_id'];
$page_title = 'Team Goals | FitCampus';
$extra_js = ['captain/team-goals.js'];
$teamGoalModel = new TeamGoalModel($pdo);
$captain_teams = $teamGoalModel->getCaptainTeams($userId);

if (empty($captain_teams)) {
    header('Location: ../member/dashboard.php');
    exit;
}

$hasRequestedTeam = array_key_exists('team_id', $_GET);
$requestedTeamId = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);
$selected_team_id = $hasRequestedTeam
    ? (($requestedTeamId && $requestedTeamId > 0) ? (int)$requestedTeamId : 0)
    : (int)$captain_teams[0]['Team_ID'];
$selected_team = null;
foreach ($captain_teams as $team) {
    if ($selected_team_id > 0 && (int)$team['Team_ID'] === $selected_team_id) {
        $selected_team = $team;
        break;
    }
}

$goals = [];
$goal_stats = ['total' => 0, 'active' => 0, 'completed' => 0, 'contributions_by_unit' => []];
if ($selected_team === null) {
    http_response_code(404);
} else {
    $goals = $teamGoalModel->getGoalsForCaptain($selected_team_id, $userId);
    $rankingsByGoal = $teamGoalModel->getContributorRankingsForCaptain($selected_team_id, $userId);
    foreach ($goals as &$goal) {
        $status = $goal['Effective_Status'];
        if ($status === 'Active') {
            $goal_stats['active']++;
        } elseif ($status === 'Completed') {
            $goal_stats['completed']++;
        }
        $unitLabel = trim($goal['Unit']);
        $unitKey = strtolower($unitLabel);
        if (!isset($goal_stats['contributions_by_unit'][$unitKey])) {
            $goal_stats['contributions_by_unit'][$unitKey] = [
                'unit' => $unitLabel,
                'value' => 0.0
            ];
        }
        $goal_stats['contributions_by_unit'][$unitKey]['value'] += (float)$goal['Total_Progress'];
        $goal['Leaderboard'] = $rankingsByGoal[(int)$goal['Team_Goal_ID']] ?? [];
        $goal['Contributions'] = $teamGoalModel->getContributionsForCaptain(
            (int)$goal['Team_Goal_ID'],
            $selected_team_id,
            $userId
        );
    }
    unset($goal);
    $goal_stats['total'] = count($goals);
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$success_message = is_string($_GET['success'] ?? null) ? $_GET['success'] : '';
$error_message = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';
?>
