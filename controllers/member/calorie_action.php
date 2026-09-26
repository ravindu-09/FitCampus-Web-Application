<?php
// backend/member/calorie_action.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Action 1: Get data for selected date (AJAX)
if ($action === 'get_date_data') {
    header('Content-Type: application/json');
    $date =$_GET['date'] ?? date('Y-m-d');

    $sum_stmt =$pdo->prepare("SELECT Calories_In, Calories_Out FROM `calorie_log` WHERE User_ID = :uid AND Date = :dt LIMIT 1");
    $sum_stmt->execute([':uid' => $user_id, ':dt' =>$date]);
    $summary =$sum_stmt->fetch(PDO::FETCH_ASSOC) ?: ['Calories_In' => 0, 'Calories_Out' => 0];

    $items_stmt =$pdo->prepare("SELECT * FROM `calorie_details` WHERE User_ID = :uid AND Date = :dt ORDER BY Detail_ID DESC");
    $items_stmt->execute([':uid' => $user_id, ':dt' =>$date]);
    $items =$items_stmt->fetchAll(PDO::FETCH_ASSOC);

    $intake_items = [];
    $burned_items = [];$totals = ['intake_cals' => 0, 'burned_cals' => 0, 'carbs' => 0, 'protein' => 0, 'fat' => 0];

    foreach ($items as$row) {
        if ($row['Type'] === 'intake') {
            $intake_items[] =$row;
            $totals['intake_cals'] += (float)$row['Calories'];
            $totals['carbs'] += (float)$row['Carbs'];
            $totals['protein'] += (float)$row['Protein'];
            $totals['fat'] += (float)$row['Fat'];
        } else {
            $burned_items[] =$row;
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
if ($action === 'add_intake' &&$_SERVER['REQUEST_METHOD'] === 'POST') {
    $date     =$_POST['log_date'] ?? date('Y-m-d');
    $category = trim($_POST['meal_category'] ?? 'Lunch');
    $item     = trim($_POST['food_item'] ?? '');
    $portion  = trim($_POST['portion'] ?? '150');
    $calories = (float)($_POST['calories'] ?? 0);
    $carbs    = (float)($_POST['carbs'] ?? 0);
    $protein  = (float)($_POST['protein'] ?? 0);
    $fat      = (float)($_POST['fat'] ?? 0);

    if (!empty($item)) {
        $ins =$pdo->prepare("INSERT INTO `calorie_details` (User_ID, Date, Type, Item_Name, Category, Portion_Or_Duration, Calories, Carbs, Protein, Fat) VALUES (:uid, :dt, 'intake', :item, :cat, :portion, :cal, :carbs, :protein, :fat)");
        $ins->execute([
            ':uid' => $user_id,
            ':dt' => $date,
            ':item' => $item,
            ':cat' => $category,
            ':portion' => $portion . 'g',
            ':cal' => $calories,
            ':carbs' => $carbs,
            ':protein' => $protein,
            ':fat' => $fat
        ]);

        $upd =$pdo->prepare("INSERT INTO `calorie_log` (User_ID, Date, Calories_In, Calories_Out) VALUES (:uid, :dt, :cal, 0) ON DUPLICATE KEY UPDATE Calories_In = Calories_In + :cal_up");
        $upd->execute([':uid' => $user_id, ':dt' =>$date, ':cal' => $calories, ':cal_up' =>$calories]);
    }

    header("Location: ../../views/member/calories.php?date=" . urlencode($date));
    exit;
}

// Action 3: Add Activity Item
if ($action === 'add_burned' &&$_SERVER['REQUEST_METHOD'] === 'POST') {
    $date     =$_POST['log_date'] ?? date('Y-m-d');
    $activity = trim($_POST['activity_type'] ?? 'Walking');$duration = (int)($_POST['duration'] ?? 30);$calories = (float)($_POST['calories_burned'] ?? ($duration * 5));

    if (!empty($activity)) {
        $ins =$pdo->prepare("INSERT INTO `calorie_details` (User_ID, Date, Type, Item_Name, Category, Portion_Or_Duration, Calories) VALUES (:uid, :dt, 'burned', :item, 'Workout', :duration, :cal)");
        $ins->execute([
            ':uid' => $user_id,
            ':dt' => $date,
            ':item' => $activity,
            ':duration' => $duration . ' min',
            ':cal' => $calories
        ]);

        $upd =$pdo->prepare("INSERT INTO `calorie_log` (User_ID, Date, Calories_In, Calories_Out) VALUES (:uid, :dt, 0, :cal) ON DUPLICATE KEY UPDATE Calories_Out = Calories_Out + :cal_up");
        $upd->execute([':uid' => $user_id, ':dt' =>$date, ':cal' => $calories, ':cal_up' =>$calories]);
    }

    header("Location: ../../views/member/calories.php?date=" . urlencode($date));
    exit;
}

// Action 4: Reset Selected Date Data (FIXED)
if ($action === 'reset_date' &&$_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $date = trim($_POST['date'] ?? '');
    
    if (!empty($date)) {
        $del =$pdo->prepare("DELETE FROM `calorie_details` WHERE User_ID = :uid AND Date = :dt");
        $del->execute([':uid' => $user_id, ':dt' =>$date]);

        $upd =$pdo->prepare("UPDATE `calorie_log` SET Calories_In = 0, Calories_Out = 0 WHERE User_ID = :uid AND Date = :dt");
        $upd->execute([':uid' => $user_id, ':dt' =>$date]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Date']);
    }
    exit;
}