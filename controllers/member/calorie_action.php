<?php
// controllers/member/calorie_action.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connection.php';
require_once '../../models/member/CalorieModel.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$calorieModel = new CalorieModel($pdo);

// Action 1: Get data for selected date (AJAX)
if ($action === 'get_date_data') {
    header('Content-Type: application/json');
    $date = $_GET['date'] ?? date('Y-m-d');

    $summary = $calorieModel->getDailySummary($user_id, $date);
    $items = $calorieModel->getDailyDetails($user_id, $date);

    $intake_items = [];
    $burned_items = [];
    $totals = ['intake_cals' => 0, 'burned_cals' => 0, 'carbs' => 0, 'protein' => 0, 'fat' => 0];

    foreach ($items as $row) {
        if ($row['Type'] === 'intake') {
            $intake_items[] = $row;
            $totals['intake_cals'] += (float)$row['Calories'];
            $totals['carbs'] += (float)$row['Carbs'];
            $totals['protein'] += (float)$row['Protein'];
            $totals['fat'] += (float)$row['Fat'];
        } else {
            $burned_items[] = $row;
            $totals['burned_cals'] += (float)$row['Calories'];
        }
    }

    echo json_encode([
        'success' => true,
        'date' => $date,
        'summary' => $summary,
        'totals' => $totals,
        'intake' => $intake_items,
        'burned' => $burned_items
    ]);
    exit;
}

// Action 2: Add Intake Item (With Manual Macros)
if ($action === 'add_intake' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $date     = $_POST['log_date'] ?? date('Y-m-d');
    $category = trim($_POST['meal_category'] ?? 'Lunch');
    $item     = trim($_POST['food_item'] ?? '');
    $portion  = trim($_POST['portion'] ?? '150');
    $calories = (float)($_POST['calories'] ?? 0);
    $carbs    = (float)($_POST['carbs'] ?? 0);
    $protein  = (float)($_POST['protein'] ?? 0);
    $fat      = (float)($_POST['fat'] ?? 0);

    if (!empty($item)) {
        $calorieModel->insertIntakeItem($user_id, $date, $item, $category, $portion, $calories, $carbs, $protein, $fat);
    }

    header("Location: ../../views/member/calories.php?date=" . urlencode($date));
    exit;
}

// Action 3: Add Activity Item
if ($action === 'add_burned' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $date     = $_POST['log_date'] ?? date('Y-m-d');
    $activity = trim($_POST['activity_type'] ?? 'Walking');
    $duration = (int)($_POST['duration'] ?? 30);
    $calories = (float)($_POST['calories_burned'] ?? ($duration * 5));

    if (!empty($activity)) {
        $calorieModel->insertBurnedItem($user_id, $date, $activity, $duration, $calories);
    }

    header("Location: ../../views/member/calories.php?date=" . urlencode($date));
    exit;
}

// Action 4: Reset Selected Date Data
if ($action === 'reset_date' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $date = trim($_POST['date'] ?? '');
    
    if (!empty($date)) {
        $calorieModel->resetDateLogs($user_id, $date);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Date']);
    }
    exit;
}
?>