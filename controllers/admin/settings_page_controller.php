<?php
// controllers/admin/settings_page_controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connection.php';
require_once '../../models/admin/SettingsModel.php';

$settingsModel = new SettingsModel($pdo);

try {
    $facilities = $settingsModel->getAllFacilities();
} catch (Exception $e) {
    $facilities = [];
}
?>