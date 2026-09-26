<?php
// controllers/captain/booking_action.php

// Initialize session and include database connection
session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/captain/BookingModel.php';

// Set header to return JSON responses
header('Content-Type: application/json');

// Verify if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$bookingModel = new BookingModel($pdo);

// Determine the requested action from GET or POST parameters
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// =========================================================================
// ACTION: Get Facilities List
// =========================================================================
if ($action === 'get_facilities') {
    try {
        $facilities = $bookingModel->getAllFacilities();
        echo json_encode(['success' => true, 'facilities' => $facilities]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// ACTION: Get Weekly Schedule
// =========================================================================
if ($action === 'get_schedule') {
    $facility_id = isset($_GET['facility_id']) ? (int)$_GET['facility_id'] : 1;
    $shift = isset($_GET['shift']) ? $_GET['shift'] : 'morning';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');

    $times = $shift === 'morning' ? 
        ['06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00'] : 
        ['12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];

    $capacity = $bookingModel->getFacilityCapacity($facility_id);
    $grid = [];

    foreach ($times as $time) {
        $display_time = date('H:i', strtotime($time));
        $row = [$display_time];
        
        for ($i = 0; $i < 7; $i++) {
            $current_date = date('Y-m-d', strtotime($start_date . " +$i days"));
            
            try {
                $total_booked = $bookingModel->getBookedTotal($facility_id, $current_date, $time);
                $has_pending = $bookingModel->getPendingCount($facility_id, $current_date, $time) > 0;

                $occupancy = ($capacity > 0) ? round(($total_booked / $capacity) * 100) : 0;
                $occupancy = ($occupancy > 100) ? 100 : $occupancy;

                if ($has_pending) {
                    $row[] = 'pending_' . $occupancy;
                } else {
                    $row[] = $occupancy;
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
// ACTION: Create Booking
// =========================================================================
if ($action === 'create_booking') {
    $facility_id = isset($_POST['facility_id']) ? (int)$_POST['facility_id'] : 1;
    $date = trim($_POST['date'] ?? '');
    
    $time_input = trim($_POST['time'] ?? '');
    $time = date('H:i:s', strtotime($time_input));
    
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 1;
    $team_id = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
    $team_size_input = isset($_POST['team_size']) ? (int)$_POST['team_size'] : 0; 
    $reason = trim($_POST['reason'] ?? '');
    $is_special = isset($_POST['is_special']) && $_POST['is_special'] === 'true';

    if (!$team_id) {
        echo json_encode(['success' => false, 'error' => 'Please select a team.']);
        exit;
    }

    if ($team_size_input <= 0) {
        echo json_encode(['success' => false, 'error' => 'Please enter a valid team size.']);
        exit;
    }

    $max_capacity = $bookingModel->getFacilityCapacity($facility_id);

    $slots_to_check = [$time];
    if ($duration == 2) {
        $slots_to_check[] = date('H:i:s', strtotime($time) + 3600);
    }

    $end_time = date('H:i:s', strtotime($time) + ($duration * 3600));

    try {
        if (!$is_special) {
            foreach ($slots_to_check as $slot_time) {
                if ($bookingModel->checkDuplicateBooking($team_id, $date, $slot_time) > 0) {
                    echo json_encode(['success' => false, 'error' => 'Your team has already booked a session during this time slot.']);
                    exit;
                }
            }

            $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
            $week_end = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
            
            $current_week_bookings = $bookingModel->getWeeklyBookingCount($team_id, $week_start, $week_end);

            if ($current_week_bookings >= 3) {
                echo json_encode(['success' => false, 'error' => 'Weekly limit (3 bookings) exceeded for this team. Submit as Special Request.']);
                exit;
            }

            foreach ($slots_to_check as $slot_time) {
                $current_booked = $bookingModel->getSlotCapacityUsage($facility_id, $date, $slot_time);
                
                if (($current_booked + $team_size_input) > $max_capacity) {
                    echo json_encode(['success' => false, 'error' => "Capacity exceeded in slot $slot_time. Submit as Special Request."]);
                    exit;
                }
            }
            $status = 'Approved';
        } else {
            if (empty($reason)) {
                echo json_encode(['success' => false, 'error' => 'Reason is required for Special Requests.']);
                exit;
            }
            $status = 'Pending';
        }

        $bookingModel->createBooking($team_id, $user_id, $facility_id, $team_size_input, $date, $time, $end_time, $status, $reason);
        
        $fac_name = $bookingModel->getFacilityName($facility_id);
        $team_name = $bookingModel->getTeamName($team_id);

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