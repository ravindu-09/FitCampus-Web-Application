<?php
/**
 * controllers/instructor/inventory_page_controller.php
 * Controller for the Equipment Status & Maintenance Console.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instructor authorization guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    $_SESSION['error'] = "Unauthorized access. Please login as an instructor.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "Equipment Status & Maintenance Console - FitCampus";
$extra_js = ["instructor/inventory.js"];

// Include database connection and model if available
require_once '../../includes/db_connection.php';
// require_once '../../models/instructor/InventoryModel.php';

// $inventoryModel = new InventoryModel($pdo);
// $equipment_list = $inventoryModel->getAllEquipment();
?>