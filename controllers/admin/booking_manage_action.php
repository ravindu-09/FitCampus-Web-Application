<?php
// controllers/admin/booking_manage_action.php

session_start();
require_once '../../includes/db_connection.php';
require_once '../../models/admin/BookingModel.php'; // Include Model
header('Content-Type: application/json');

// Admin Security Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$admin_id = $_SESSION['user_id'];
$bookingModel = new BookingModel($pdo);

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

    $capacity = $bookingModel->getFacilityCapacity($facility_id);
    $grid = [];

    foreach ($times as $time) {
        $display_time = date('H:i', strtotime($time));
        $row = [$display_time];
        
        for ($i = 0; $i < 7; $i++) {
            $current_date = date('Y-m-d', strtotime($start_date . " +$i days"));
            
            try {
                $total_booked = $bookingModel->getBookedSize($facility_id, $current_date, $time);
                $occupancy = ($capacity > 0) ? round(($total_booked / $capacity) * 100) : 0;
                $occupancy = ($occupancy > 100) ? 100 : $occupancy;

                $row[] = $occupancy;
            } catch (Exception $e) {
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
        $bookings = $bookingModel->getSlotDetails($facility_id, $date, $time);
        echo json_encode(['success' => true, 'bookings' => $bookings]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// ACTION: Get Pending Special Requests
// =========================================================================
if ($action === 'get_pending') {
    try {
        $requests = $bookingModel->getPendingRequests();
        echo json_encode(['success' => true, 'requests' => $requests]);
    } catch (Exception $e) {
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
        $pending = $bookingModel->getBookingById($booking_id);

        if (!$pending) {
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
            exit;
        }

        $max_capacity = $bookingModel->getFacilityCapacity($pending['Facility_ID']);
        $current_booked = $bookingModel->getApprovedBookedSizeForConflict($pending['Facility_ID'], $pending['Reserve_Date'], $pending['Start_Time']);
        $new_total = $current_booked + (int)$pending['Team_Size'];

        if ($new_total > $max_capacity) {
            $occupants = $bookingModel->getOccupantsForConflict($pending['Facility_ID'], $pending['Reserve_Date'], $pending['Start_Time']);

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
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// =========================================================================
// ACTION: Finalize Approval
// =========================================================================
if ($action === 'approve_booking') {
    $booking_id = (int)$_POST['booking_id'];
    $cancel_ids = isset($_POST['cancel_ids']) ? json_decode($_POST['cancel_ids']) : [];
    $cancel_reason = trim($_POST['cancel_reason'] ?? '');

    $result = $bookingModel->approveBookingWithConflicts($booking_id, $admin_id, $cancel_ids, $cancel_reason);
    echo json_encode($result);
    exit;
}

// =========================================================================
// ACTION: Decline Booking
// =========================================================================
if ($action === 'decline_booking') {
    $booking_id = (int)$_POST['booking_id'];
    $reject_reason = trim($_POST['reject_reason'] ?? '');
    
    $result = $bookingModel->declineBooking($booking_id, $admin_id, $reject_reason);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>