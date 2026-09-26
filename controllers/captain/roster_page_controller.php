<?php
// controllers/captain/roster_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Team Roster | FitCampus";

$extra_js = [
    "captain/roster.js"
];

require_once '../../includes/db_connection.php';
require_once '../../models/captain/RosterModel.php';

$rosterModel = new RosterModel($pdo);

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Get team_id from GET request
$selected_team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;

// GET CAPTAIN'S TEAMS
$captain_teams = $rosterModel->getCaptainTeams($user_id);

// Default to the first team if no team is selected
if ($selected_team_id == 0 && !empty($captain_teams)) {
    $selected_team_id = $captain_teams[0]['Team_ID'];
}

$team_members = [];
$selected_team_name = "";
$selected_team_sport = "";
$selected_team_gender = "";

if ($selected_team_id > 0) {
    $team_info = $rosterModel->getTeamInfo($selected_team_id);
    if ($team_info) {
        $selected_team_name = $team_info['Team_Name'];
        $selected_team_sport = $team_info['Sport'];
        $selected_team_gender = $team_info['Sport_Gender'];
    }
    $team_members = $rosterModel->getTeamMembers($selected_team_id);
}
?>