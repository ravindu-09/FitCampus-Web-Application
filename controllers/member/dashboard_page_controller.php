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
?>