<?php
// controllers/admin/BookingController.php

session_start();
require_once '../../includes/db_connection.php';
require_once '../../bll/admin/BookingBLL.php';

header('Content-Type: application/json');

// Admin Security Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$admin_id = $_SESSION['user_id'];

$bookingBLL = new BookingBLL($pdo);

switch ($action) {
    case 'get_schedule':
        $facility_id = isset($_GET['facility_id']) ? (int)$_GET['facility_id'] : 1;
        $shift = $_GET['shift'] ?? 'morning';
        $start_date = $_GET['start_date'] ?? date('Y-m-d');
        echo json_encode($bookingBLL->getScheduleGrid($facility_id, $shift, $start_date));
        break;

    case 'get_slot_details':
        $facility_id = (int)$_GET['facility_id'];
        $date = $_GET['date'];
        $time = $_GET['time'];
        echo json_encode($bookingBLL->getSlotDetails($facility_id, $date, $time));
        break;

    case 'get_pending':
        echo json_encode($bookingBLL->getPendingRequests());
        break;

    case 'check_conflict':
        $booking_id = (int)$_POST['booking_id'];
        echo json_encode($bookingBLL->checkConflict($booking_id));
        break;

    case 'approve_booking':
        $booking_id = (int)$_POST['booking_id'];
        $cancel_ids = isset($_POST['cancel_ids']) ? json_decode($_POST['cancel_ids'], true) : [];
        $cancel_reason = trim($_POST['cancel_reason'] ?? '');
        echo json_encode($bookingBLL->approveBooking($booking_id, $admin_id, $cancel_ids, $cancel_reason));
        break;

    case 'decline_booking':
        $booking_id = (int)$_POST['booking_id'];
        $reject_reason = trim($_POST['reject_reason'] ?? '');
        echo json_encode($bookingBLL->declineBooking($booking_id, $admin_id, $reject_reason));
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}