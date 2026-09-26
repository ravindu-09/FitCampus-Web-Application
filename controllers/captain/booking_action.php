<?php
// backend/captain/booking_action.php

// Initialize session and include database connection
session_start();
require_once '../../includes/db_connection.php';

// Set header to return JSON responses
header('Content-Type: application/json');

// Verify if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Determine the requested action from GET or POST parameters
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// =========================================================================
// ACTION: Get Facilities List
// Fetches all available facilities to populate frontend toggle buttons
// =========================================================================
if ($action === 'get_facilities') {
    try {
        $stmt = $pdo->query("SELECT Facility_ID, Facility_Name, Location, Capacity FROM facility ORDER BY Facility_ID ASC");
        $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'facilities' => $facilities]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// ACTION: Get Weekly Schedule
// Generates a 7-day grid showing slot occupancy and pending status
// =========================================================================
if ($action === 'get_schedule') {
    // Retrieve parameters with fallback defaults
    $facility_id = isset($_GET['facility_id']) ? (int)$_GET['facility_id'] : 1;
    $shift = isset($_GET['shift']) ? $_GET['shift'] : 'morning';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');

    // Define time slots based on the selected shift (morning vs evening)
    $times = $shift === 'morning' ? 
        ['06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00'] : 
        ['12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];

    // Fetch the maximum capacity of the requested facility
    $stmtCap = $pdo->prepare("SELECT Capacity FROM facility WHERE Facility_ID = ?");
    $stmtCap->execute([$facility_id]);
    $capacity = $stmtCap->fetchColumn() ?: 50;
    
    $grid = [];

    // Loop through each time slot to calculate occupancy for the next 7 days
    foreach ($times as $time) {
        $display_time = date('H:i', strtotime($time));
        $row = [$display_time];
        
        for ($i = 0; $i < 7; $i++) {
            $current_date = date('Y-m-d', strtotime($start_date . " +$i days"));
            
            try {
                // Calculate total confirmed members booked for this specific slot
                $stmtBooked = $pdo->prepare("
                    SELECT COALESCE(SUM(b.Team_Size), 0) as total_booked 
                    FROM booking b 
                    WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND ? >= b.Start_Time AND ? < b.End_Time AND b.Status = 'Approved'
                ");
                $stmtBooked->execute([$facility_id, $current_date, $time, $time]);
                $total_booked = $stmtBooked->fetchColumn();
                
                // Check if there are any pending special requests for this slot
                $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE Facility_ID = ? AND Reserve_Date = ? AND ? >= Start_Time AND ? < End_Time AND Status = 'Pending'");
                $stmtPending->execute([$facility_id, $current_date, $time, $time]);
                $has_pending = $stmtPending->fetchColumn() > 0;

                // Calculate occupancy percentage, capped at 100%
                $occupancy = ($capacity > 0) ? round(($total_booked / $capacity) * 100) : 0;
                $occupancy = ($occupancy > 100) ? 100 : $occupancy;

                // Format the cell response: append 'pending_' prefix if requests are awaiting approval
                if ($has_pending) {
                    $row[] = 'pending_' . $occupancy;
                } else {
                    $row[] = $occupancy;
                }
            } catch (PDOException $e) {
                // Fallback to 0 occupancy on database error
                $row[] = 0; 
            }
        }
        $grid[] = $row;
    }

    echo json_encode(['success' => true, 'capacity' => $capacity, 'gridData' => $grid]);
    exit;
}

// =========================================================================
// ACTION: Create Booking
// Validates limits, capacity, and duplicates before inserting a booking
// =========================================================================
if ($action === 'create_booking') {
    // Sanitize and prepare incoming POST data
    $facility_id = isset($_POST['facility_id']) ? (int)$_POST['facility_id'] : 1;
    $date = trim($_POST['date'] ?? '');
    
    // Ensure time string is properly formatted (HH:MM:SS)
    $time_input = trim($_POST['time'] ?? '');
    $time = date('H:i:s', strtotime($time_input));
    
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 1;
    $team_id = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
    $team_size_input = isset($_POST['team_size']) ? (int)$_POST['team_size'] : 0; 
    $reason = trim($_POST['reason'] ?? '');
    $is_special = isset($_POST['is_special']) && $_POST['is_special'] === 'true';

    // Basic validation
    if (!$team_id) {
        echo json_encode(['success' => false, 'error' => 'Please select a team.']);
        exit;
    }

    if ($team_size_input <= 0) {
        echo json_encode(['success' => false, 'error' => 'Please enter a valid team size.']);
        exit;
    }

    // Fetch facility capacity for backend validation
    $stmtCap = $pdo->prepare("SELECT Capacity FROM facility WHERE Facility_ID = ?");
    $stmtCap->execute([$facility_id]);
    $max_capacity = $stmtCap->fetchColumn() ?: 50;

    // Determine the exact slots required based on the selected duration (1hr or 2hr)
    $slots_to_check = [$time];
    if ($duration == 2) {
        $slots_to_check[] = date('H:i:s', strtotime($time) + 3600);
    }

    $end_time = date('H:i:s', strtotime($time) + ($duration * 3600));

    try {
        // Run strict validations only for standard (non-special) requests
        if (!$is_special) {
            
            // 1. Duplicate Check: Ensure the team hasn't already booked these exact slots
            foreach ($slots_to_check as $slot_time) {
                $stmtDup = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE Team_ID = ? AND Reserve_Date = ? AND ? >= Start_Time AND ? < End_Time AND Status IN ('Approved', 'Pending')");
                $stmtDup->execute([$team_id, $date, $slot_time, $slot_time]);
                if ($stmtDup->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'error' => 'Your team has already booked a session during this time slot.']);
                    exit;
                }
            }

            // 2. Weekly Limit Check: Restrict to maximum 3 standard bookings per week
            $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
            $week_end = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
            
            $stmtLimit = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE Team_ID = ? AND Status IN ('Approved', 'Pending') AND Reserve_Date BETWEEN ? AND ?");
            $stmtLimit->execute([$team_id, $week_start, $week_end]);
            $current_week_bookings = $stmtLimit->fetchColumn();

            if ($current_week_bookings >= 3) {
                echo json_encode(['success' => false, 'error' => 'Weekly limit (3 bookings) exceeded for this team. Submit as Special Request.']);
                exit;
            }

            // 3. Capacity Check: Ensure the facility can accommodate the team size for all selected slots
            foreach ($slots_to_check as $slot_time) {
                $stmtCapCheck = $pdo->prepare("
                    SELECT COALESCE(SUM(b.Team_Size), 0) 
                    FROM booking b 
                    WHERE b.Facility_ID = ? AND b.Reserve_Date = ? AND ? >= b.Start_Time AND ? < b.End_Time AND b.Status = 'Approved'
                ");
                $stmtCapCheck->execute([$facility_id, $date, $slot_time, $slot_time]);
                $current_booked = $stmtCapCheck->fetchColumn();
                
                if (($current_booked + $team_size_input) > $max_capacity) {
                    echo json_encode(['success' => false, 'error' => "Capacity exceeded in slot $slot_time. Submit as Special Request."]);
                    exit;
                }
            }
            $status = 'Approved'; // Standard bookings are auto-approved
        } else {
            // Special requests bypass capacity and limits but require a reason
            if (empty($reason)) {
                echo json_encode(['success' => false, 'error' => 'Reason is required for Special Requests.']);
                exit;
            }
            $status = 'Pending'; // Special requests require admin approval
        }

        // Insert the booking record into the database
        $stmt = $pdo->prepare("
            INSERT INTO booking (Team_ID, Requested_By, Facility_ID, Team_Size, Booking_Time, Reserve_Date, Start_Time, End_Time, Status, Exception_Reason) 
            VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$team_id, $user_id, $facility_id, $team_size_input, $date, $time, $end_time, $status, $reason]);
        
        // Fetch Facility and Team names to return to the frontend for dynamic table updates
        $stmtFacName = $pdo->prepare("SELECT Facility_Name FROM facility WHERE Facility_ID = ?");
        $stmtFacName->execute([$facility_id]);
        $fac_name = $stmtFacName->fetchColumn();

        $stmtTeamName = $pdo->prepare("SELECT Team_Name FROM team WHERE Team_ID = ?");
        $stmtTeamName->execute([$team_id]);
        $team_name = $stmtTeamName->fetchColumn();

        // Return success response with newly created booking details
        echo json_encode([
            'success' => true, 
            'status' => $status,
            'new_booking' => [
                'Facility_Name' => $fac_name,
                'Team_Name' => $team_name,
                'Reserve_Date' => $date,
                'Time_Slot' => date('H:i', strtotime($time)) . ' - ' . date('H:i', strtotime($end_time)),
                'Status' => $status
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>