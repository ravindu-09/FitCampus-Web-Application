<?php
// controllers/instructor/kiosk_page_controller.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instructor authorization guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    $_SESSION['error'] = "Unauthorized access. Please login as an instructor.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "Attendance Kiosk - FitCampus";
$extra_js = ["instructor/kiosk.js"];

// Include database connection and model
require_once '../../includes/db_connection.php';
require_once '../../models/instructor/KioskModel.php';

$kioskModel = new KioskModel($pdo);

// Fetch required data for the kiosk view
$occupancy_data = $kioskModel->getLiveOccupancy();
$attendance_logs = $kioskModel->getRecentAttendance();
?>