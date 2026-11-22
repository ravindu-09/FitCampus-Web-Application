<?php
// controllers/member/teams_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$session_role = strtolower($_SESSION['role'] ?? '');
if (!in_array($session_role, ['member', 'student'], true)) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$page_title = 'Team Hub | FitCampus';

$extra_js = [
    "member/teams.js?v=" . hash_file('sha256', __DIR__ . '/../../assets/js/member/teams.js')
];

require_once '../../includes/db_connection.php';
require_once '../../models/member/TeamModel.php';
require_once '../../models/member/WorkoutModel.php';
require_once '../../models/common/TeamGoalModel.php';

$teamModel = new TeamModel($pdo);
$teamGoalModel = new TeamGoalModel($pdo);
$teams = $teamModel->getTeamsByUser($user_id);
$workoutModel = new WorkoutModel($pdo);
$team_workouts = [];
$scheduled_sessions = [];
$team_goals = [];
$selected_team = null;
$team_access_denied = false;

$requested_team_id = null;
if (isset($_GET['team_id'])) {
    $requested_team_id = filter_var($_GET['team_id'], FILTER_VALIDATE_INT);
    if ($requested_team_id === false || $requested_team_id < 1) {
        $team_access_denied = true;
    }
} elseif (!empty($teams)) {
    $requested_team_id = (int)$teams[0]['Team_ID'];
}

if (!$team_access_denied && $requested_team_id !== null) {
    foreach ($teams as $team) {
        if ((int)$team['Team_ID'] === $requested_team_id) {
            $selected_team = $team;
            break;
        }
    }

    if ($selected_team === null) {
        $team_access_denied = true;
    } else {
        $team_workouts = $workoutModel->getWorkoutsForTeamMember($requested_team_id, $user_id);
        $scheduled_sessions = $teamModel->getUpcomingApprovedBookingsForTeam($requested_team_id, $user_id);
        $team_goals = $teamGoalModel->getGoalsForMember($requested_team_id, $user_id);
        $rankings_by_goal = $teamGoalModel->getContributorRankingsForMember($requested_team_id, $user_id);
        foreach ($team_goals as &$goal) {
            $goal['Can_Contribute'] = $goal['Effective_Status'] === 'Active'
                && $goal['Start_Date'] <= date('Y-m-d')
                && $goal['End_Date'] >= date('Y-m-d');
            $goal['Leaderboard'] = $rankings_by_goal[(int)$goal['Team_Goal_ID']] ?? [];
            $goal['Contributions'] = $teamGoalModel->getContributionsForMember(
                (int)$goal['Team_Goal_ID'],
                $requested_team_id,
                $user_id
            );
        }
        unset($goal);
    }
}

if ($team_access_denied) {
    http_response_code(404);
}

$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$team_goal_success = is_string($_GET['success'] ?? null) ? $_GET['success'] : '';
$team_goal_error = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';
?>