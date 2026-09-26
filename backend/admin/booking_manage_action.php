<?php
// backend/admin/booking_manage_action.php

session_start();
require_once '../../includes/db_connection.php';
header('Content-Type: application/json');

// Admin Security Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$admin_id = $_SESSION['user_id'];

// =========================================================================
// ACTION: Get Weekly Schedule (Grid Calendar Data)
// =========================================================================
if ($action === 'get_schedule') {
    $facility_id = isset($_GET['facility_id']) ? (int)$_GET['facility_id'] : 1;
    $shift = isset($_GET['shift']) ? $_GET['shift'] : 'morning';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');

    $times = $shift === 'morning' ? 
        ['06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00'] : 
        ['12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];

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
                $stmtBooked = $pdo->prepare("
                    SELECT COALESCE(SUM(b.Team_Size), 0) as total_booked 
                    FROM booking b 
                    WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND ? >= b.Start_Time AND ? < b.End_Time AND b.Status = 'Approved'
                ");
                $stmtBooked->execute([$facility_id, $current_date, $time, $time]);
                $total_booked = $stmtBooked->fetchColumn();

                $occupancy = ($capacity > 0) ? round(($total_booked / $capacity) * 100) : 0;
                $occupancy = ($occupancy > 100) ? 100 : $occupancy;

                $row[] = $occupancy;
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
// ACTION: Get Slot Specific Details
// =========================================================================
if ($action === 'get_slot_details') {
    $facility_id = (int)$_GET['facility_id'];
    $date = $_GET['date'];
    $time = $_GET['time'];

    try {
        $stmt = $pdo->prepare("
            SELECT b.*, t.Team_Name, u.First_Name, u.Last_Name, us.Registration_Number 
            FROM booking b
            JOIN team t ON b.Team_ID = t.Team_ID
            JOIN user u ON b.Requested_By = u.User_ID
            LEFT JOIN university_student us ON u.User_ID = us.User_ID
            WHERE b.Facility_ID = ? AND b.Reserve_Date = ? 
            AND (? >= b.Start_Time AND ? < b.End_Time)
            AND b.Status IN ('Approved', 'Pending')
            ORDER BY b.Status ASC, b.Start_Time ASC
        ");
        $stmt->execute([$facility_id, $date, $time, $time]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'bookings' => $bookings]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// ACTION: Get Pending Special Requests
// =========================================================================
if ($action === 'get_pending') {
    try {
        $stmt = $pdo->query("
            SELECT b.*, f.Facility_Name, t.Team_Name, u.First_Name, u.Last_Name 
            FROM booking b
            JOIN facility f ON b.Facility_ID = f.Facility_ID
            JOIN team t ON b.Team_ID = t.Team_ID
            JOIN user u ON b.Requested_By = u.User_ID
            WHERE b.Status = 'Pending'
            ORDER BY b.Booking_Time DESC
        ");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'requests' => $requests]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// ACTION: Check for Conflicts Before Approval
// =========================================================================
if ($action === 'check_conflict') {
    $booking_id = (int)$_POST['booking_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT Facility_ID, Reserve_Date, Start_Time, End_Time, Team_Size FROM booking WHERE Booking_ID = ?");
        $stmt->execute([$booking_id]);
        $pending = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pending) {
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
            exit;
        }

        $stmtCap = $pdo->prepare("SELECT Capacity FROM facility WHERE Facility_ID = ?");
        $stmtCap->execute([$pending['Facility_ID']]);
        $max_capacity = $stmtCap->fetchColumn() ?: 50;

        $stmtBooked = $pdo->prepare("
            SELECT COALESCE(SUM(Team_Size), 0) FROM booking 
            WHERE Facility_ID = ? AND Reserve_Date = ? AND Status = 'Approved'
            AND (? >= Start_Time AND ? < End_Time)
        ");
        $stmtBooked->execute([$pending['Facility_ID'], $pending['Reserve_Date'], $pending['Start_Time'], $pending['Start_Time']]);
        $current_booked = (int)$stmtBooked->fetchColumn();

        $new_total = $current_booked + (int)$pending['Team_Size'];

        if ($new_total > $max_capacity) {
            $stmtOccupants = $pdo->prepare("
                SELECT b.Booking_ID, t.Team_Name, b.Team_Size, b.Start_Time, b.End_Time 
                FROM booking b
                JOIN team t ON b.Team_ID = t.Team_ID
                WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND b.Status = 'Approved'
                AND (? >= b.Start_Time AND ? < b.End_Time)
            ");
            $stmtOccupants->execute([$pending['Facility_ID'], $pending['Reserve_Date'], $pending['Start_Time'], $pending['Start_Time']]);
            $occupants = $stmtOccupants->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'has_conflict' => true,
                'max_capacity' => $max_capacity,
                'current_booked' => $current_booked,
                'pending_size' => $pending['Team_Size'],
                'overflow' => $new_total - $max_capacity,
                'occupants' => $occupants
            ]);
        } else {
            echo json_encode(['success' => true, 'has_conflict' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// ACTION: Finalize Approval (And notify respective users/teams)
// =========================================================================
if ($action === 'approve_booking') {
    $booking_id = (int)$_POST['booking_id'];
    $cancel_ids = isset($_POST['cancel_ids']) ? json_decode($_POST['cancel_ids']) : [];
    $cancel_reason = trim($_POST['cancel_reason'] ?? '');

    try {
        $pdo->beginTransaction();

        $stmtNotiUser = $pdo->prepare("INSERT INTO notification (User_ID, Title, Message) VALUES (?, ?, ?)");
        $stmtNotiTeam = $pdo->prepare("INSERT INTO notification (Team_ID, Title, Message) VALUES (?, ?, ?)");

        // 1. If admin cancelled existing bookings to make room
        if (!empty($cancel_ids)) {
            $inQuery = implode(',', array_fill(0, count($cancel_ids), '?'));
            $stmtCancel = $pdo->prepare("UPDATE booking SET Status = 'Cancelled', Exception_Reason = ? WHERE Booking_ID IN ($inQuery)");
            $params = array_merge([$cancel_reason], $cancel_ids);
            $stmtCancel->execute($params);

            // Fetch DISTINCT team members of cancelled bookings to send ONE notification per team
            $stmtTeams = $pdo->prepare("SELECT DISTINCT Team_ID FROM booking WHERE Booking_ID IN ($inQuery)");
            $stmtTeams->execute($cancel_ids);
            $cancelled_team_ids = $stmtTeams->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($cancelled_team_ids)) {
                foreach($cancelled_team_ids as $team_id) {
                    $msg = "Your team's booking was cancelled due to a facility override. Reason: " . $cancel_reason;
                    $stmtNotiTeam->execute([$team_id, 'Booking Cancelled', $msg]);
                }
            }
        }

        // 2. Approve the pending special request
        $stmtApprove = $pdo->prepare("UPDATE booking SET Status = 'Approved', Approved_By = ? WHERE Booking_ID = ?");
        $stmtApprove->execute([$admin_id, $booking_id]);

        // Fetch Booking details (Team Name, Reserve Date, Start Time, End Time, Requested_By, Team_ID)
        $stmtReq = $pdo->prepare("
            SELECT b.Requested_By, b.Team_ID, b.Reserve_Date, b.Start_Time, b.End_Time, t.Team_Name 
            FROM booking b
            JOIN team t ON b.Team_ID = t.Team_ID
            WHERE b.Booking_ID = ?
        ");
        $stmtReq->execute([$booking_id]);
        $req_data = $stmtReq->fetch(PDO::FETCH_ASSOC);

        if ($req_data) {
            $team_name = $req_data['Team_Name'];
            $reserve_date = $req_data['Reserve_Date'];
            $start_time = date('h:i A', strtotime($req_data['Start_Time']));
            $end_time = date('h:i A', strtotime($req_data['End_Time']));

            // Enhanced message including Team Name and Booking Time
            $msg = "Your Special Request for team '{$team_name}' on {$reserve_date} from {$start_time} to {$end_time} has been Approved.";
            
            // Send to the specific Captain who requested (or use Team_ID if you want the whole team to see it)
            $stmtNotiUser->execute([$req_data['Requested_By'], 'Special Request Approved', $msg]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// ACTION: Decline Booking (And notify team)
// =========================================================================
if ($action === 'decline_booking') {
    $booking_id = (int)$_POST['booking_id'];
    $reject_reason = trim($_POST['reject_reason'] ?? '');
    
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE booking SET Status = 'Rejected', Approved_By = ?, Exception_Reason = ? WHERE Booking_ID = ?");
        $stmt->execute([$admin_id, $reject_reason, $booking_id]);

        $stmtNotiTeam = $pdo->prepare("INSERT INTO notification (Team_ID, Title, Message) VALUES (?, ?, ?)");

        // Fetch team and booking details for the rejected booking
        $stmtTeam = $pdo->prepare("
            SELECT b.Team_ID, b.Reserve_Date, b.Start_Time, b.End_Time, t.Team_Name 
            FROM booking b
            JOIN team t ON b.Team_ID = t.Team_ID
            WHERE b.Booking_ID = ?
        ");
        $stmtTeam->execute([$booking_id]);
        $booking_info = $stmtTeam->fetch(PDO::FETCH_ASSOC);

        // Send ONE notification attached to the Team_ID with Team Name and Booking Time details
        if ($booking_info) {
            $team_id = $booking_info['Team_ID'];
            $team_name = $booking_info['Team_Name'];
            $reserve_date = $booking_info['Reserve_Date'];
            $start_time = date('h:i A', strtotime($booking_info['Start_Time']));
            $end_time = date('h:i A', strtotime($booking_info['End_Time']));

            $msg = "Your team '{$team_name}' special request for {$reserve_date} ({$start_time} - {$end_time}) was Rejected. Reason: " . $reject_reason;
            $stmtNotiTeam->execute([$team_id, 'Special Request Rejected', $msg]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Fallback error
echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>