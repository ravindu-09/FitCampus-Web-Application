<?php
// controllers/captain/booking_page_controller.php

// Check session status and initiate if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect unauthorized users to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Facilities Booking | FitCampus";

// Include page-specific JS script
$extra_js = [
    "captain/booking.js"
];

require_once '../../includes/db_connection.php';
require_once '../../models/captain/BookingModel.php';

$bookingModel = new BookingModel($pdo);

// Fetch all teams where the current user holds the 'Captain' role
$captain_teams = $bookingModel->getCaptainTeams($user_id);

// Fetch all facilities to generate toggle buttons
$facilities = $bookingModel->getAllFacilities();

// Fetch the booking history specific to the logged-in captain
$booking_history = $bookingModel->getBookingHistory($user_id);
$captain_team_ids = array_fill_keys(array_map('intval', array_column($captain_teams, 'Team_ID')), true);

foreach ($booking_history as &$booking) {
    $booking['can_assign_workout'] = $booking['Status'] === 'Approved'
        && isset($captain_team_ids[(int)$booking['Team_ID']]);
    $booking['assigned_workouts'] = [];
    $booking['available_workouts'] = [];

    if ($booking['can_assign_workout']) {
        $booking['assigned_workouts'] = $bookingModel->getAssignedWorkoutsForBooking(
            (int)$booking['Booking_ID'],
            (int)$user_id
        );
        $booking['available_workouts'] = $bookingModel->getAvailableWorkoutsForBooking(
            (int)$booking['Booking_ID'],
            (int)$user_id
        );
    }
}
unset($booking);
?>