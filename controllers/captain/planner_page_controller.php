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
    "captain/planner.js" // UI logic and future AJAX calls
];

require_once '../../includes/db_connection.php';
require_once '../../models/captain/PlannerModel.php';

$plannerModel = new PlannerModel($pdo);
$user_id = $_SESSION['user_id'];
$workouts = $plannerModel->getTeamWorkoutsByUser($user_id);

// ---------------------------------------------------------
// MOCK DATA:
// ---------------------------------------------------------
$mock_teams = [
    ['Team_ID' => 1, 'Team_Name' => 'UOC Track & Field'],
    ['Team_ID' => 2, 'Team_Name' => 'UOC Swimming Team']
];

$mock_published = [
    ['id' => 101, 'title' => 'Explosive Power Training', 'target' => 'Oct 24, 2026', 'location' => 'Main Gym', 'icon' => 'fitness_center'],
    ['id' => 102, 'title' => 'Endurance Conditioning', 'target' => 'Oct 26, 2026', 'location' => 'Arena-A', 'icon' => 'directions_run']
];

$mock_drafts = [
    ['id' => 201, 'title' => 'Recovery Flow Session', 'target' => 'Nov 01, 2026', 'location' => 'Pool-Side', 'icon' => 'edit_note']
];
// ---------------------------------------------------------

$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>