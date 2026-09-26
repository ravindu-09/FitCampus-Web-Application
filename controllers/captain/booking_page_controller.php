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
?>