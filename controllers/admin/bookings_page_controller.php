<?php
// controllers/admin/bookings_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connection.php';
require_once '../../models/admin/BookingModel.php';

$bookingModel = new BookingModel($pdo);

try {
    $facilities = $bookingModel->getAllFacilities();
} catch (Exception $e) {
    $facilities = [];
}
?>