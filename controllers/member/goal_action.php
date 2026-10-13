<?php
// controllers/member/goal_action.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connection.php';
require_once '../../models/member/GoalModel.php';

// Check whether user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$goalModel = new GoalModel($pdo);

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ---------------------------------------------------------
// ACTION: CREATE GOAL
// ---------------------------------------------------------
if ($action === 'create_goal' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $goal_name = trim($_POST['goal_name'] ?? '');
    $goal_type = trim($_POST['goal_type'] ?? '');

    $target = isset($_POST['target']) && $_POST['target'] !== ''
        ? max(0, (float) $_POST['target'])
        : null;

    $unit = trim($_POST['unit'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    // Automatically set when the goal is created
    $start_date = date('Y-m-d');
    $status = 'Active';

    // -----------------------------------------------------
    // START VALUE / CURRENT VALUE
    // -----------------------------------------------------

    if ($goal_type === 'Weight Loss') {

        // Starting weight entered by the user
        $start_value = isset($_POST['start_value']) &&
                       $_POST['start_value'] !== ''
            ? max(0, (float) $_POST['start_value'])
            : null;

        // At creation, current weight equals starting weight.
        // Therefore progress starts at 0%.
        $current_value = $start_value;

    } else {

        // Strength / Endurance goals start from zero
        $start_value = 0;
        $current_value = 0;
    }

    // -----------------------------------------------------
    // BASIC VALIDATION
    // -----------------------------------------------------

    if (
        $goal_name === '' ||
        $goal_type === '' ||
        $target === null ||
        $unit === '' ||
        $end_date === ''
    ) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Please fill in all required fields.")
        );
        exit;
    }

    if ($goal_type === 'Weight Loss' && $start_value === null) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Please enter your starting weight.")
        );
        exit;
    }

    if ($target <= 0) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Target value must be greater than zero.")
        );
        exit;
    }

    if (
        $goal_type === 'Weight Loss' &&
        $start_value <= 0
    ) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Starting weight must be greater than zero.")
        );
        exit;
    }

    if (
        $goal_type === 'Weight Loss' &&
        $target >= $start_value
    ) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Target weight must be lower than starting weight.")
        );
        exit;
    }

    if ($end_date < $start_date) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Target date cannot be before the start date.")
        );
        exit;
    }

    // -----------------------------------------------------
    // CREATE GOAL
    // -----------------------------------------------------

    try {

        $created = $goalModel->createGoal(
            $user_id,
            $goal_name,
            $goal_type,
            $target,
            $start_value,
            $current_value,
            $unit,
            $start_date,
            $end_date,
            $status
        );

        if ($created) {
            header(
                "Location: ../../views/member/goals.php?success=" .
                urlencode("Goal created successfully.")
            );
            exit;
        }

        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Unable to create goal.")
        );
        exit;

    } catch (PDOException $e) {

        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Database error while creating goal.")
        );
        exit;
    }
}

// ---------------------------------------------------------
// ACTION: UPDATE PROGRESS
// ---------------------------------------------------------
if ($action === 'update_progress' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $goal_id = isset($_POST['goal_id'])
        ? (int) $_POST['goal_id']
        : 0;

    $value = isset($_POST['current_value']) &&
             $_POST['current_value'] !== ''
        ? (float) $_POST['current_value']
        : null;

    $update_date = trim($_POST['update_date'] ?? '');

    // -----------------------------------------------------
    // BASIC VALIDATION
    // -----------------------------------------------------

    if (
        $goal_id <= 0 ||
        $value === null ||
        $update_date === ''
    ) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Please fill in all progress fields.")
        );
        exit;
    }

    if ($value < 0) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Progress value cannot be negative.")
        );
        exit;
    }

    // -----------------------------------------------------
    // VERIFY GOAL OWNERSHIP
    // -----------------------------------------------------

    $goal = $goalModel->getGoalByIdAndUser(
        $goal_id,
        $user_id
    );

    if (!$goal) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Goal not found.")
        );
        exit;
    }

    // -----------------------------------------------------
    // DATE VALIDATION
    // -----------------------------------------------------

    $goal_start_date = $goal['Start_Date'] ?? '';

    if (
        $goal_start_date !== '' &&
        $update_date < $goal_start_date
    ) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Progress date cannot be before the goal start date.")
        );
        exit;
    }

    $today = date('Y-m-d');

    if ($update_date > $today) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Progress date cannot be in the future.")
        );
        exit;
    }

    // -----------------------------------------------------
    // WEIGHT LOSS VALIDATION
    // -----------------------------------------------------

    if ($goal['Goal_Type'] === 'Weight Loss') {

        $start_value = (float) $goal['Start_Value'];

        if ($value > $start_value) {
            header(
                "Location: ../../views/member/goals.php?error=" .
                urlencode(
                    "Current weight cannot be greater than the starting weight."
                )
            );
            exit;
        }
    }

    // -----------------------------------------------------
    // SAVE PROGRESS
    // -----------------------------------------------------

    try {

        $updated = $goalModel->updateGoalProgress(
            $goal_id,
            $user_id,
            $value,
            $update_date
        );

        if ($updated) {
            header(
                "Location: ../../views/member/goals.php?success=" .
                urlencode("Progress updated successfully.")
            );
            exit;
        }

        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Unable to update progress.")
        );
        exit;

    } catch (PDOException $e) {

        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Database error while updating progress.")
        );
        exit;
    }
}
// ---------------------------------------------------------
// ACTION: DELETE GOAL
// ---------------------------------------------------------
if ($action === 'delete_goal' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $goal_id = isset($_POST['goal_id'])
        ? (int) $_POST['goal_id']
        : 0;

    if ($goal_id <= 0) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Invalid goal.")
        );
        exit;
    }

    // Verify that this goal belongs to the logged-in user
    $goal = $goalModel->getGoalByIdAndUser(
        $goal_id,
        $user_id
    );

    if (!$goal) {
        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Goal not found.")
        );
        exit;
    }

    try {

        $deleted = $goalModel->deleteGoal(
            $goal_id,
            $user_id
        );

        if ($deleted) {
            header(
                "Location: ../../views/member/goals.php?success=" .
                urlencode("Goal deleted successfully.")
            );
            exit;
        }

        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Unable to delete goal.")
        );
        exit;

    } catch (PDOException $e) {

        header(
            "Location: ../../views/member/goals.php?error=" .
            urlencode("Database error while deleting goal.")
        );
        exit;
    }
}

// ---------------------------------------------------------
// INVALID ACTION
// ---------------------------------------------------------

header("Location: ../../views/member/goals.php");
exit;
?>