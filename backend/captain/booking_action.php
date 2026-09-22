<?php
// backend/captain/booking_action.php
session_start();
require_once '../../includes/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// =========================================================================
// 1. GET SCHEDULE DATA
// =========================================================================
if ($action === 'get_schedule') {
    $facility_id = isset($_GET['facility_id']) ? (int)$_GET['facility_id'] : 1;
    $shift = isset($_GET['shift']) ? $_GET['shift'] : 'morning';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');

    $times = $shift === 'morning' ? 
        ['06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00'] : 
        ['12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];

    // Fetch Max Capacity from Facility table
    $stmtCap = $pdo->prepare("SELECT Capacity FROM facility WHERE Facility_ID = ?");
    $stmtCap->execute([$facility_id]);
    $capacity = $stmtCap->fetchColumn() ?: 50;
    
    $grid = [];

    foreach ($times as $time) {
        $display_time = date('H:i', strtotime($time));
        $row = [$display_time];
        
        for ($i = 0; $i < 7; $i++) {
            $current_date = date('Y-m-d', strtotime($start_date . " +$i days"));
            
            try {
                // Calculate Total Booked Capacity based on team member counts
                $stmtBooked = $pdo->prepare("
                    SELECT SUM((SELECT COUNT(*) FROM team_member WHERE Team_ID = b.Team_ID)) as total_booked 
                    FROM booking b 
                    WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND b.Start_Time = ? AND b.Status = 'Approved'
                ");
                $stmtBooked->execute([$facility_id, $current_date, $time]);
                $total_booked = $stmtBooked->fetchColumn() ?: 0;
                
                // Check if any pending requests exist
                $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE Facility_ID = ? AND Reserve_Date = ? AND Start_Time = ? AND Status = 'Pending'");
                $stmtPending->execute([$facility_id, $current_date, $time]);
                $has_pending = $stmtPending->fetchColumn() > 0;

                if ($has_pending && $total_booked == 0) {
                    $row[] = 'pending';
                } else {
                    $occupancy = ($capacity > 0) ? round(($total_booked / $capacity) * 100) : 0;
                    $row[] = ($occupancy > 100) ? 100 : $occupancy;
                }
            } catch (PDOException $e) {
                $row[] = 0; 
            }
        }
        $grid[] = $row;
    }

    echo json_encode(['success' => true, 'capacity' => $capacity, 'gridData' => $grid]);
    exit;
}

// =========================================================================
// 2. CREATE BOOKING
// =========================================================================
if ($action === 'create_booking') {
    $facility_id = isset($_POST['facility_id']) ? (int)$_POST['facility_id'] : 1;
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '') . ':00'; // Make it SQL TIME format
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 1;
    $team_id = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
    $team_size_input = isset($_POST['team_size']) ? (int)$_POST['team_size'] : 0; // Only for validation
    $reason = trim($_POST['reason'] ?? '');
    $is_special = isset($_POST['is_special']) && $_POST['is_special'] === 'true';

    // End time calculation
    $end_time = date('H:i:s', strtotime($time) + ($duration * 3600));

    if (!$team_id) {
        echo json_encode(['success' => false, 'error' => 'Please select a team.']);
        exit;
    }

    // Get Facility Capacity
    $stmtCap = $pdo->prepare("SELECT Capacity FROM facility WHERE Facility_ID = ?");
    $stmtCap->execute([$facility_id]);
    $max_capacity = $stmtCap->fetchColumn() ?: 50;

    try {
        // A. Weekly Limit Check (Mon-Sun)
        $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
        $week_end = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
        
        $stmtLimit = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE Requested_By = ? AND Status IN ('Approved', 'Pending') AND Reserve_Date BETWEEN ? AND ?");
        $stmtLimit->execute([$user_id, $week_start, $week_end]);
        $current_week_bookings = $stmtLimit->fetchColumn();

        // B. Check Current Booked Capacity
        $stmtCapCheck = $pdo->prepare("
            SELECT COALESCE(SUM((SELECT COUNT(*) FROM team_member WHERE Team_ID = b.Team_ID)), 0) 
            FROM booking b 
            WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND b.Start_Time = ? AND b.Status = 'Approved'
        ");
        $stmtCapCheck->execute([$facility_id, $date, $time]);
        $current_booked = $stmtCapCheck->fetchColumn() ?: 0;
        
        $projected_capacity = $current_booked + $team_size_input;

        if (!$is_special) {
            // Standard Booking Validation
            if ($current_week_bookings >= 3) {
                echo json_encode(['success' => false, 'error' => 'Weekly limit (3) exceeded. Submit as Special Request.']);
                exit;
            }
            if ($projected_capacity > $max_capacity) {
                echo json_encode(['success' => false, 'error' => "Capacity exceeded (Only " . ($max_capacity - $current_booked) . " spots left). Submit as Special Request."]);
                exit;
            }
            $status = 'Approved';
        } else {
            // Special Request Validation
            if (empty($reason)) {
                echo json_encode(['success' => false, 'error' => 'Reason is required for Special Requests.']);
                exit;
            }
            $status = 'Pending';
        }

        // C. Insert into Booking Table
        $stmt = $pdo->prepare("
            INSERT INTO booking (Team_ID, Requested_By, Facility_ID, Booking_Time, Reserve_Date, Start_Time, End_Time, Status, Exception_Reason) 
            VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$team_id, $user_id, $facility_id, $date, $time, $end_time, $status, $reason]);
        
        echo json_encode(['success' => true, 'status' => $status]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>