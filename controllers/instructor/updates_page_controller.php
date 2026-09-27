<?php
// controllers/instructor/updates_page_controller.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instructor authorization guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    $_SESSION['error'] = "Unauthorized access. Please login as an instructor.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$page_title = "Staff Updates Console - FitCampus";
$extra_js = ["instructor/updates.js"];

require_once '../../includes/db_connection.php';
?>