<?php
// controllers/instructor/rules_page_controller.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instructor authorization guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    $_SESSION['error'] = "Unauthorized access. Please login as an instructor.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$page_title = "Gym Rules Manager - FitCampus";
$extra_js = ["instructor/rules.js"];

require_once '../../includes/db_connection.php';
// require_once '../../models/instructor/RulesModel.php';
// $rulesModel = new RulesModel($pdo);
// $rules_list = $rulesModel->getAllRules();
?>