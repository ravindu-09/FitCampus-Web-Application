<?php
// controllers/member/dashboard_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Database Connection & Model
require_once __DIR__ . '/../../includes/db_connection.php';
require_once __DIR__ . '/../../models/member/DashboardModel.php';

$dashboardModel = new DashboardModel($pdo);

/* 1. User Attendance / Check-In Status */
$attendance = $dashboardModel->getAttendanceStatus($user_id);

$is_checked_in = $attendance && (int)$attendance['Active_Status'] === 1;
$location_name = $is_checked_in ? $attendance['Facility_Name'] : 'Not in Facility';
$checkin_time  = $is_checked_in ? date('h:i A', strtotime($attendance['Check_In_Time'])) : '--:--';

/* 2. Facility Live Capacity & Load Calculation */
$raw_facilities = $dashboardModel->getLiveFacilities();

$facilities = [];
foreach ($raw_facilities as $idx => $fac) {
    $capacity = max(1, (int)$fac['Capacity']);
    $current  = (int)$fac['live_count'];
    $percentage = min(100, round(($current / $capacity) * 100));

    $facilities[] = [
        'id'          => $fac['Facility_ID'],
        'name'        => $fac['Facility_Name'],
        'capacity'    => $capacity,
        'current'     => $current,
        'percentage'  => $percentage,
        'color_class' => ($idx % 2 === 0) ? 'primary' : 'secondary',
        'is_glow'     => ($idx === 0)
    ];
}

/* 3. Gym Rules & Regulations */
$rules = $dashboardModel->getGymRules();

/* 4. Latest Announcements & Motivation */
$announcements = $dashboardModel->getAnnouncements();

$is_captain = isset($_SESSION['is_captain']) && (int)$_SESSION['is_captain'] === 1;
$captain_teams = [];
$captain_active_goals = [];
$captain_upcoming_sessions = [];
if ($is_captain) {
    require_once __DIR__ . '/../../models/common/TeamGoalModel.php';
    require_once __DIR__ . '/../../models/member/TeamModel.php';

    $teamGoalModel = new TeamGoalModel($pdo);
    $teamModel = new TeamModel($pdo);
    $captain_teams = $teamGoalModel->getCaptainTeams((int)$user_id);
    foreach ($captain_teams as $team) {
        $teamId = (int)$team['Team_ID'];
        foreach ($teamGoalModel->getGoalsForCaptain($teamId, (int)$user_id) as $goal) {
            if ($goal['Effective_Status'] === 'Active') {
                $captain_active_goals[] = [
                    'Team_Name' => $team['Team_Name'],
                    'Team_ID' => $teamId,
                    'Goal_Name' => $goal['Goal_Name'],
                    'End_Date' => $goal['End_Date'],
                    'Progress' => (float)$goal['Total_Progress'],
                    'Target' => (float)$goal['Target_Value'],
                    'Progress_Percentage' => (int)$goal['Progress_Percentage'],
                    'Unit' => $goal['Unit']
                ];
            }
        }
        foreach ($teamModel->getUpcomingApprovedBookingsForTeam($teamId, (int)$user_id) as $session) {
            $session['Team_Name'] = $team['Team_Name'];
            $session['Team_ID'] = $teamId;
            $captain_upcoming_sessions[] = $session;
        }
    }

    usort($captain_active_goals, static function ($a, $b) {
        return strcmp($a['End_Date'], $b['End_Date']);
    });
    usort($captain_upcoming_sessions, static function ($a, $b) {
        return strcmp($a['Reserve_Date'] . ' ' . $a['Start_Time'], $b['Reserve_Date'] . ' ' . $b['Start_Time']);
    });
    $captain_active_goals = array_slice($captain_active_goals, 0, 3);
    $captain_upcoming_sessions = array_slice($captain_upcoming_sessions, 0, 3);
}
?>