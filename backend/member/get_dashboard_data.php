<?php
// backend/member/get_dashboard_data.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Database Connection
require_once __DIR__ . '/../../includes/db_connection.php';

/* 1. User Attendance / Check-In Status */
$checkin_stmt = $pdo->prepare("
    SELECT a.Check_In_Time, a.Active_Status, f.Facility_Name, f.Location
    FROM `attendance` a
    JOIN `facility` f ON a.Facility_ID = f.Facility_ID
    WHERE a.User_ID = :uid
    ORDER BY a.Check_In_Time DESC
    LIMIT 1
");
$checkin_stmt->execute([':uid' => $user_id]);
$attendance = $checkin_stmt->fetch();

$is_checked_in = $attendance && (int)$attendance['Active_Status'] === 1;
$location_name = $is_checked_in ? $attendance['Facility_Name'] : 'Not in Facility';
$checkin_time  = $is_checked_in ? date('h:i A', strtotime($attendance['Check_In_Time'])) : '--:--';

/* 2. Facility Live Capacity & Load Calculation */
$facilities_stmt = $pdo->query("
    SELECT f.Facility_ID, f.Facility_Name, f.Capacity,
           (SELECT COUNT(*) 
            FROM `attendance` a 
            WHERE a.Facility_ID = f.Facility_ID AND a.Active_Status = 1) AS live_count
    FROM `facility` f
    ORDER BY f.Facility_ID ASC
    LIMIT 2
");
$raw_facilities = $facilities_stmt->fetchAll();

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
$rules_stmt = $pdo->query("
    SELECT Rule_No, Rule, Penalty_For_Violation 
    FROM `gym_rule` 
    ORDER BY Rule_No ASC 
    LIMIT 6
");
$rules = $rules_stmt->fetchAll();

/* 4. Latest Announcements & Motivation */
$announcements_stmt = $pdo->query("
    SELECT Title, Description, Publish_Date 
    FROM `announcement` 
    ORDER BY Publish_Date DESC 
    LIMIT 2
");
$announcements = $announcements_stmt->fetchAll();